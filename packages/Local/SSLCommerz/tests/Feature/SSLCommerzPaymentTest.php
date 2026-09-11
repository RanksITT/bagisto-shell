<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Local\SSLCommerz\Payment\SSLCommerz;
use Webkul\Checkout\Facades\Cart;
use Webkul\Core\Models\CoreConfig;
use Webkul\Sales\Models\Invoice;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderTransaction;

beforeEach(function () {
    Http::preventStrayRequests();

    foreach ([
        'active' => '1',
        'sandbox' => '1',
        'store_id' => 'testbox',
        'store_password' => 'qwerty',
    ] as $field => $value) {
        CoreConfig::factory()->create([
            'code' => 'sales.payment_methods.sslcommerz.'.$field,
            'value' => $value,
            'channel_code' => 'default',
        ]);
    }

    $this->sslCommerzMock = $this->mock(SSLCommerz::class)->makePartial();

    $this->app->instance(SSLCommerz::class, $this->sslCommerzMock);
});

it('redirects to cart when the credentials are missing', function () {
    // Arrange
    $this->sslCommerzMock->shouldReceive('hasValidCredentials')->andReturn(false);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error');
});

it('redirects to cart when the cart is not found', function () {
    // Arrange
    Cart::shouldReceive('getCart')->andReturn(null);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error');
});

it('redirects to cart when the currency is one sslcommerz does not settle', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'JPY']);

    Cart::shouldReceive('getCart')->andReturn($cart);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error');
});

it('redirects to cart when the total is below what sslcommerz accepts', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $cart->base_grand_total = 5.00;

    Cart::shouldReceive('getCart')->andReturn($cart);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error');
});

it('sends the customer to the hosted checkout without recording anything of its own', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    Cart::shouldReceive('getCart')->andReturn($cart);

    $this->sslCommerzMock->shouldReceive('initiatePayment')
        ->andReturn([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcdea15f1',
        ]);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/testcdea15f1');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('reports a failure when sslcommerz will not open a session', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    Cart::shouldReceive('getCart')->andReturn($cart);

    $this->sslCommerzMock->shouldReceive('initiatePayment')->andReturn(null);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error', trans('sslcommerz::app.response.payment-failed'));
});

it('shows the customer the reason sslcommerz gave for refusing the session', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    Cart::shouldReceive('getCart')->andReturn($cart);

    $this->sslCommerzMock->shouldReceive('initiatePayment')->andReturn([
        'status' => 'FAILED',
        'failedreason' => 'Transaction amount is not allowed as per admin configuration!',
    ]);

    // Act
    $response = $this->get(route('sslcommerz.redirect'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error', trans('sslcommerz::app.response.session-refused', [
        'reason' => 'Transaction amount is not allowed as per admin configuration!',
    ]));
});

it('settles the payment and creates the order with an invoice from the callback', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    // Act
    $response = $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Assert
    $response->assertRedirect();

    $order = Order::where('cart_id', $cart->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('processing');

    $orderTransaction = OrderTransaction::where('order_id', $order->id)->first();

    expect($orderTransaction)->not->toBeNull()
        ->and($orderTransaction->transaction_id)->toBe('SSL'.$cart->id.'TTEST')
        ->and($orderTransaction->status)->toBe('VALID')
        ->and((float) $orderTransaction->amount)->toBe((float) $order->base_grand_total);

    expect(Invoice::where('order_id', $order->id)->first())->not->toBeNull();

    $cart->refresh();

    expect($cart->is_active)->toBe(0);
});

it('does not start a session when receiving the callback', function () {
    // A session started here would replace the customer's own, signing them out of the store.
    $response = $this->post(route('sslcommerz.callback'));

    // Assert
    expect($response->headers->getCookies())->toBeEmpty();
});

it('places no order when the callback is not signed by sslcommerz', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $this->sslCommerzMock->shouldReceive('verifySignature')->andReturn(false);

    $this->sslCommerzMock->shouldReceive('validateTransaction')->never();

    // Act
    $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Assert
    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('believes the validation api over the posted status when the two disagree', function () {
    // Arrange
    // The post says the payment was taken, the gateway says it failed. The gateway wins.
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $this->sslCommerzMock->shouldReceive('verifySignature')->andReturn(true);

    $this->sslCommerzMock->shouldReceive('validateTransaction')
        ->andReturn(validationResponse($cart, ['status' => 'FAILED']));

    // Act
    $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Assert
    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('places no order when the validated payment is for another transaction', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $this->sslCommerzMock->shouldReceive('verifySignature')->andReturn(true);

    $this->sslCommerzMock->shouldReceive('validateTransaction')
        ->andReturn(validationResponse($cart, ['tran_id' => 'SSL999TSOMEONEELSE']));

    // Act
    $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Assert
    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('shows the customer the order the callback placed', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    $callback = $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Act
    $response = $this->get($callback->headers->get('Location'));

    // Assert
    $response->assertRedirect(route('shop.checkout.onepage.success'));

    $response->assertSessionHas('order_id', Order::where('cart_id', $cart->id)->first()->id);
});

it('tells the customer their cancelled payment was cancelled', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $callback = $this->post(route('sslcommerz.callback'), [
        'status' => 'CANCELLED',
        'tran_id' => 'SSL'.$cart->id.'TTEST',
    ]);

    // Act
    $response = $this->get($callback->headers->get('Location'));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('warning');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('cannot be made to show an order by typing a reference', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Act
    $response = $this->get(route('sslcommerz.complete', [
        'tran_id' => 'SSL'.$cart->id.'TTEST',
        'status' => 'VALID',
    ]));

    // Assert
    $response->assertRedirect(route('shop.checkout.cart.index'));

    $response->assertSessionHas('error');

    $response->assertSessionMissing('order_id');
});

it('creates the order from an ipn when the customer never came back', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertOk();

    $response->assertJsonPath('status', 'order_created');

    expect(Order::where('cart_id', $cart->id)->first())->not->toBeNull();
});

it('does not create a second order when the ipn arrives after the callback', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    $this->post(route('sslcommerz.callback'), postedPayment($cart));

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertOk();

    $response->assertJsonPath('status', 'order_already_exists');

    expect(Order::where('cart_id', $cart->id)->count())->toBe(1);
});

it('ignores an ipn that is not signed by sslcommerz', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    $this->sslCommerzMock->shouldReceive('verifySignature')->andReturn(false);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertOk();

    $response->assertJsonPath('status', 'signature_mismatch');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('ignores an ipn for a payment that was not taken', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart, ['status' => 'FAILED']));

    // Assert
    $response->assertOk();

    $response->assertJsonPath('status', 'ignored');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('refuses to place the order when the cart no longer totals what was taken', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart, [
        'currency_amount' => (string) ($cart->base_grand_total + 100),
    ]);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    // Acknowledged, so that SSLCommerz stops redelivering something retrying cannot fix.
    $response->assertOk();

    $response->assertJsonPath('status', 'payment_not_confirmed');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('refuses to place the order when the currency taken is not the one charged', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart, ['currency_type' => 'USD']);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertOk();

    $response->assertJsonPath('status', 'payment_not_confirmed');

    expect(Order::where('cart_id', $cart->id)->first())->toBeNull();
});

it('settles against the totals collect totals recalculated, not the stale ones it was handed', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    /**
     * Drift the stored totals away from what the items add up to. collectTotals() recomputes
     * them from the items, so the figures on the cart instance read back before it runs are
     * the drifted ones, and the figures it leaves behind are the true ones.
     */
    DB::table('cart')->where('id', $cart->id)->update([
        'grand_total' => $cart->grand_total + 500,
        'base_grand_total' => $cart->base_grand_total + 500,
        'sub_total' => $cart->sub_total + 500,
        'base_sub_total' => $cart->base_sub_total + 500,
    ]);

    mockConfirmedPayment($this->sslCommerzMock, $cart);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertOk();

    $order = Order::where('cart_id', $cart->id)->first();

    expect($order)->not->toBeNull()
        ->and((float) $order->base_grand_total)->toBe((float) $cart->base_grand_total);
});

it('leaves a risky payment as an order to verify, placed but not invoiced', function () {
    // Arrange
    $cart = $this->createCartWithItems('sslcommerz', ['base_currency_code' => 'BDT']);

    mockConfirmedPayment($this->sslCommerzMock, $cart, [
        'risk_level' => '1',
        'risk_title' => 'Risky',
    ]);

    // Act
    $response = $this->postJson(route('sslcommerz.ipn'), postedPayment($cart));

    // Assert
    $response->assertJsonPath('status', 'order_created');

    $order = Order::where('cart_id', $cart->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->status)->toBe('pending');

    expect(Invoice::where('order_id', $order->id)->first())->toBeNull();
});

/**
 * The fields SSLCommerz posts back to the store, in the shape it really posts them. The signature
 * is checked by the payment method, which the tests stand in for, so only the references that
 * decide which payment this is need to be real here.
 */
function postedPayment($cart, array $overrides = []): array
{
    return array_merge([
        'status' => 'VALID',
        'tran_id' => 'SSL'.$cart->id.'TTEST',
        'val_id' => '2609111234567',
        'amount' => (string) $cart->base_grand_total,
        'currency' => 'BDT',
        'bank_tran_id' => '2609111234567abc',
        'card_type' => 'BKASH-BKash',
        'verify_key' => 'amount,bank_tran_id,card_type,currency,status,tran_id,val_id',
        'verify_sign' => 'signature-checked-by-the-payment-method',
    ], $overrides);
}

/**
 * The body the validation API answers with, in the shape it really sends. That call is what an
 * order is built from, so a payment is simulated by answering it rather than by what is posted.
 */
function validationResponse($cart, array $overrides = []): array
{
    return array_merge([
        'status' => 'VALID',
        'tran_id' => 'SSL'.$cart->id.'TTEST',
        'val_id' => '2609111234567',
        'amount' => (string) $cart->base_grand_total,
        'store_amount' => (string) $cart->base_grand_total,
        'currency' => 'BDT',
        'currency_type' => 'BDT',
        'currency_amount' => (string) $cart->base_grand_total,
        'bank_tran_id' => '2609111234567abc',
        'card_type' => 'BKASH-BKash',
        'risk_level' => '0',
        'risk_title' => 'Safe',
        'APIConnect' => 'DONE',
    ], $overrides);
}

/**
 * Answer the signature check and the validation call for a payment taken on the given cart.
 */
function mockConfirmedPayment($sslCommerzMock, $cart, array $overrides = []): void
{
    $sslCommerzMock->shouldReceive('verifySignature')->andReturn(true);

    $sslCommerzMock->shouldReceive('validateTransaction')
        ->with('2609111234567')
        ->andReturn(validationResponse($cart, $overrides));
}
