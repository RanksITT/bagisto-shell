<?php

use Illuminate\Support\Facades\Http;
use Local\SSLCommerz\Payment\SSLCommerz;
use Webkul\Core\Models\CoreConfig;

beforeEach(function () {
    $this->sslCommerz = app(SSLCommerz::class);
});

it('returns the correct payment method code', function () {
    // Act
    $code = $this->sslCommerz->getCode();

    // Assert
    expect($code)->toBe('sslcommerz');
});

it('returns the payment method title from configuration', function () {
    // Arrange
    CoreConfig::factory()->create([
        'code' => 'sales.payment_methods.sslcommerz.title',
        'value' => 'Pay with SSLCommerz',
        'channel_code' => 'default',
        'locale_code' => 'en',
    ]);

    // Act
    $title = $this->sslCommerz->getTitle();

    // Assert
    expect($title)->toBe('Pay with SSLCommerz');
});

it('returns the payment method description from configuration', function () {
    // Arrange
    CoreConfig::factory()->create([
        'code' => 'sales.payment_methods.sslcommerz.description',
        'value' => 'bKash, Nagad, Rocket and cards',
        'channel_code' => 'default',
        'locale_code' => 'en',
    ]);

    // Act
    $description = $this->sslCommerz->getDescription();

    // Assert
    expect($description)->toBe('bKash, Nagad, Rocket and cards');
});

it('returns the store credentials from configuration', function () {
    // Arrange
    configureCredentials();

    // Act & Assert
    expect($this->sslCommerz->getStoreId())->toBe('testbox')
        ->and($this->sslCommerz->getStorePassword())->toBe('qwerty');
});

it('returns the sandbox host when sandbox mode is enabled', function () {
    // Arrange
    configureCredentials(['sandbox' => '1']);

    // Act & Assert
    expect($this->sslCommerz->isSandbox())->toBeTrue()
        ->and($this->sslCommerz->getBaseUrl())->toBe(SSLCommerz::SANDBOX_URL);
});

it('returns the production host when sandbox mode is disabled', function () {
    // Arrange
    configureCredentials(['sandbox' => '0']);

    // Act & Assert
    expect($this->sslCommerz->isSandbox())->toBeFalse()
        ->and($this->sslCommerz->getBaseUrl())->toBe(SSLCommerz::PRODUCTION_URL);
});

it('is not available until both credentials are configured', function () {
    // Arrange
    CoreConfig::factory()->create([
        'code' => 'sales.payment_methods.sslcommerz.active',
        'value' => '1',
        'channel_code' => 'default',
    ]);

    configureCredentials(['store_password' => '']);

    // Act & Assert
    expect($this->sslCommerz->hasValidCredentials())->toBeFalse()
        ->and($this->sslCommerz->isAvailable())->toBeFalse();
});

it('settles in the currencies sslcommerz accepts, regardless of case', function () {
    // Act & Assert
    expect($this->sslCommerz->isCurrencySupported('bdt'))->toBeTrue()
        ->and($this->sslCommerz->isCurrencySupported('USD'))->toBeTrue()
        ->and($this->sslCommerz->isCurrencySupported('JPY'))->toBeFalse();
});

it('charges in the store currency rather than the one the customer is browsing in', function () {
    // A cart shown as $1 against a BDT store is charged as the base total, in BDT.
    $cart = cartStub(['cart_currency_code' => 'USD']);

    expect($this->sslCommerz->getCurrency($cart))->toBe('BDT');
});

it('holds a bdt payment to the range sslcommerz accepts', function () {
    // Act & Assert
    expect($this->sslCommerz->isAmountAccepted(9.99, 'BDT'))->toBeFalse()
        ->and($this->sslCommerz->isAmountAccepted(10.00, 'BDT'))->toBeTrue()
        ->and($this->sslCommerz->isAmountAccepted(500000.00, 'BDT'))->toBeTrue()
        ->and($this->sslCommerz->isAmountAccepted(500000.01, 'BDT'))->toBeFalse();
});

it('leaves a payment in another currency for sslcommerz to judge', function () {
    // The limits are quoted in BDT and the conversion rate is SSLCommerz's own, so a USD
    // amount cannot be measured against them here.
    expect($this->sslCommerz->isAmountAccepted(1.00, 'USD'))->toBeTrue();
});

it('generates a transaction id carrying the cart id', function () {
    // Act
    $tranId = $this->sslCommerz->generateTransactionId(42);

    // Assert
    expect($tranId)->toStartWith('SSL42T')
        ->and(strlen($tranId))->toBeLessThanOrEqual(30)
        ->and($tranId)->not->toBe($this->sslCommerz->generateTransactionId(42));
});

it('reads the cart out of a transaction id', function () {
    expect($this->sslCommerz->parseCartId('SSL28TPQWIXAHJ3F'))->toBe(28)
        ->and($this->sslCommerz->parseCartId('nonsense'))->toBeNull()
        ->and($this->sslCommerz->parseCartId(null))->toBeNull();
});

it('returns the payment method image from config', function () {
    // Arrange
    CoreConfig::factory()->create([
        'code' => 'sales.payment_methods.sslcommerz.image',
        'value' => 'sslcommerz/custom-logo.png',
        'channel_code' => 'default',
    ]);

    // Act
    $image = $this->sslCommerz->getImage();

    // Assert
    expect($image)->toContain('sslcommerz/custom-logo.png');
});

it('inlines the bundled logo when no image is configured', function () {
    // The package ships outside the shop theme, so it has nothing in the theme build to link to.
    expect($this->sslCommerz->getImage())->toStartWith('data:image/png;base64,');
});

it('returns the correct redirect URL', function () {
    // Act & Assert
    expect($this->sslCommerz->getRedirectUrl())->toBe(route('sslcommerz.redirect'));
});

it('reads the gateway page url out of an opened session', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'failedreason' => '',
            'sessionkey' => 'A15F1AA5CBF1F0F7969043779A4C8247',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcdea15f1',
        ], 200),
    ]);

    // Act
    $session = $this->sslCommerz->initiatePayment(cartStub(), 'SSL1TTEST');

    // Assert
    expect($this->sslCommerz->getGatewayPageUrl($session))->toBe('https://sandbox.sslcommerz.com/EasyCheckOut/testcdea15f1')
        ->and($this->sslCommerz->getFailureReason($session))->toBeNull()
        ->and($session['sessionkey'])->toBe('A15F1AA5CBF1F0F7969043779A4C8247');
});

it('sends the payment sslcommerz needs to open a session', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/gwprocess/v4/api.php' => Http::response([
            'status' => 'SUCCESS',
            'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testcdea15f1',
        ], 200),
    ]);

    // Act
    $this->sslCommerz->initiatePayment(cartStub(), 'SSL1TTEST');

    // Assert
    Http::assertSent(function ($request) {
        $data = $request->data();

        return $data['store_id'] === 'testbox'
            && $data['store_passwd'] === 'qwerty'
            && $data['tran_id'] === 'SSL1TTEST'
            && $data['total_amount'] === '338.95'
            && $data['currency'] === 'BDT'
            && $data['value_a'] === 1
            && $data['success_url'] === route('sslcommerz.callback')
            && $data['fail_url'] === route('sslcommerz.callback')
            && $data['cancel_url'] === route('sslcommerz.callback')
            && $data['ipn_url'] === route('sslcommerz.ipn');
    });
});

it('hands back the reason sslcommerz gives for refusing to open a session', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/gwprocess/v4/api.php' => Http::response([
            'status' => 'FAILED',
            'failedreason' => 'Transaction amount is not allowed as per admin configuration!',
        ], 200),
    ]);

    // Act
    $session = $this->sslCommerz->initiatePayment(cartStub(), 'SSL1TTEST');

    // Assert
    expect($this->sslCommerz->getGatewayPageUrl($session))->toBeNull()
        ->and($this->sslCommerz->getFailureReason($session))->toBe('Transaction amount is not allowed as per admin configuration!');
});

it('finds nowhere to send the customer when the session response carries no gateway page', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/gwprocess/v4/api.php' => Http::response(['status' => 'SUCCESS'], 200),
    ]);

    // Act
    $session = $this->sslCommerz->initiatePayment(cartStub(), 'SSL1TTEST');

    // Assert
    expect($this->sslCommerz->getGatewayPageUrl($session))->toBeNull()
        ->and($this->sslCommerz->getFailureReason($session))->toBeNull();
});

it('returns nothing when sslcommerz cannot be reached', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/gwprocess/v4/api.php' => Http::response('Service Unavailable', 503),
    ]);

    // Act & Assert
    expect($this->sslCommerz->initiatePayment(cartStub(), 'SSL1TTEST'))->toBeNull();
});

it('reads a payment back out of the validation api', function () {
    // Arrange
    configureCredentials();

    Http::fake([
        '*/validator/api/validationserverAPI.php*' => Http::response([
            'status' => 'VALID',
            'tran_id' => 'SSL1TTEST',
            'val_id' => '2609111234567',
            'amount' => '338.95',
            'currency' => 'BDT',
            'currency_type' => 'BDT',
            'currency_amount' => '338.95',
            'bank_tran_id' => '2609111234567abc',
            'risk_level' => '0',
            'APIConnect' => 'DONE',
        ], 200),
    ]);

    // Act
    $validation = $this->sslCommerz->validateTransaction('2609111234567');

    // Assert
    expect($validation['status'])->toBe('VALID')
        ->and($validation['tran_id'])->toBe('SSL1TTEST');

    Http::assertSent(fn ($request) => $request['val_id'] === '2609111234567'
        && $request['store_id'] === 'testbox'
        && $request['store_passwd'] === 'qwerty');
});

it('returns nothing without calling sslcommerz when there is no validation id', function () {
    // Arrange
    Http::fake();

    // Act & Assert
    expect($this->sslCommerz->validateTransaction(null))->toBeNull();

    Http::assertNothingSent();
});

it('verifies the signature sslcommerz posts its outcome with', function () {
    // Arrange
    configureCredentials();

    $payload = signedPayload();

    // Act & Assert
    expect($this->sslCommerz->verifySignature($payload))->toBeTrue();
});

it('refuses a signature that does not match the posted fields', function () {
    // Arrange
    configureCredentials();

    $payload = signedPayload();

    $payload['amount'] = '99999.00';

    // Act & Assert
    expect($this->sslCommerz->verifySignature($payload))->toBeFalse();
});

it('refuses a post that carries no signature at all', function () {
    // Arrange
    configureCredentials();

    // Act & Assert
    expect($this->sslCommerz->verifySignature(['status' => 'VALID', 'tran_id' => 'SSL1TTEST']))->toBeFalse();
});

it('refuses a signature made with another store password', function () {
    // Arrange
    configureCredentials(['store_password' => 'someone-elses-password']);

    // Act & Assert
    expect($this->sslCommerz->verifySignature(signedPayload()))->toBeFalse();
});

it('reads the amount and currency the payment was taken in', function () {
    // The transaction currency is what the store asked for; `amount` is its BDT conversion.
    $validation = [
        'amount' => '2800.00',
        'currency' => 'BDT',
        'currency_type' => 'usd',
        'currency_amount' => '25.50',
    ];

    expect($this->sslCommerz->getPaidAmount($validation))->toBe(25.50)
        ->and($this->sslCommerz->getPaidCurrency($validation))->toBe('USD');
});

it('falls back to the settled amount when no transaction currency is reported', function () {
    $validation = ['amount' => '338.95', 'currency' => 'BDT'];

    expect($this->sslCommerz->getPaidAmount($validation))->toBe(338.95)
        ->and($this->sslCommerz->getPaidCurrency($validation))->toBe('BDT');
});

it('reads the risk flag sslcommerz sets on a payment', function () {
    expect($this->sslCommerz->isRisky(['risk_level' => '1']))->toBeTrue()
        ->and($this->sslCommerz->isRisky(['risk_level' => '0']))->toBeFalse()
        ->and($this->sslCommerz->isRisky([]))->toBeFalse();
});

/**
 * Configure a complete, usable set of credentials, so that a test only has to say which single
 * value it wants broken.
 */
function configureCredentials(array $overrides = []): void
{
    $credentials = array_merge([
        'store_id' => 'testbox',
        'store_password' => 'qwerty',
        'sandbox' => '1',
    ], $overrides);

    foreach ($credentials as $field => $value) {
        CoreConfig::factory()->create([
            'code' => 'sales.payment_methods.sslcommerz.'.$field,
            'value' => $value,
            'channel_code' => 'default',
        ]);
    }
}

/**
 * A payment posted back by SSLCommerz, signed the way it really signs one: the fields named in
 * `verify_key`, paired with their values, plus the store password as an md5 of itself, sorted
 * by field name and joined before hashing.
 */
function signedPayload(array $overrides = []): array
{
    $payload = array_merge([
        'status' => 'VALID',
        'tran_id' => 'SSL1TTEST',
        'val_id' => '2609111234567',
        'amount' => '338.95',
        'currency' => 'BDT',
        'bank_tran_id' => '2609111234567abc',
        'card_type' => 'BKASH-BKash',
    ], $overrides);

    $payload['verify_key'] = 'amount,bank_tran_id,card_type,currency,status,tran_id,val_id';

    $signed = [];

    foreach (explode(',', $payload['verify_key']) as $field) {
        $signed[$field] = $payload[$field];
    }

    $signed['store_passwd'] = md5('qwerty');

    ksort($signed);

    $hashables = [];

    foreach ($signed as $field => $value) {
        $hashables[] = $field.'='.$value;
    }

    $payload['verify_sign'] = md5(implode('&', $hashables));

    return $payload;
}

/**
 * The smallest thing `initiatePayment` will accept: it reads the totals, the currency, the items
 * and the addresses off the cart, and a cart without addresses simply sends no customer details.
 */
function cartStub(array $overrides = []): object
{
    return new class($overrides)
    {
        public $id = 1;

        public $grand_total = 338.95;

        public $base_grand_total = 338.95;

        public $cart_currency_code = 'BDT';

        public $base_currency_code = 'BDT';

        public $items_qty = 1;

        public $customer_first_name = 'Rafiq';

        public $customer_last_name = 'Islam';

        public $customer_email = null;

        public $billing_address = null;

        public $shipping_address = null;

        public $items;

        public function __construct(array $overrides = [])
        {
            $this->items = collect([(object) ['name' => 'Helmet']]);

            foreach ($overrides as $property => $value) {
                $this->{$property} = $value;
            }
        }
    };
}
