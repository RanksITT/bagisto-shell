<?php

namespace Local\SSLCommerz\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Local\SSLCommerz\Enums\SSLCommerzPaymentStatus;
use Local\SSLCommerz\Payment\SSLCommerz;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Sales\Contracts\Order as OrderContract;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Shop\Http\Controllers\Controller;

class SSLCommerzController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SSLCommerz $sslCommerz,
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    /**
     * Open a payment session for the cart and send the customer to SSLCommerz's hosted checkout.
     */
    public function redirect(): RedirectResponse
    {
        if (! $this->sslCommerz->hasValidCredentials()) {
            session()->flash('error', trans('sslcommerz::app.response.provide-credentials'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $cart = Cart::getCart();

        if (! $cart) {
            session()->flash('error', trans('sslcommerz::app.response.cart-not-found'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $currency = $this->sslCommerz->getCurrency($cart);

        if (! $this->sslCommerz->isCurrencySupported($currency)) {
            session()->flash('error', trans('sslcommerz::app.response.supported-currency-error', [
                'currency' => $currency,
                'supportedCurrencies' => implode(', ', $this->sslCommerz->getSupportedCurrencies()),
            ]));

            return redirect()->route('shop.checkout.cart.index');
        }

        if (! $this->sslCommerz->isAmountAccepted((float) $cart->base_grand_total, $currency)) {
            session()->flash('error', trans('sslcommerz::app.response.amount-out-of-range', [
                'min' => core()->formatBasePrice(SSLCommerz::MINIMUM_AMOUNT),
                'max' => core()->formatBasePrice(SSLCommerz::MAXIMUM_AMOUNT),
                'amount' => core()->formatBasePrice((float) $cart->base_grand_total),
            ]));

            return redirect()->route('shop.checkout.cart.index');
        }

        $session = $this->sslCommerz->initiatePayment(
            $cart,
            $this->sslCommerz->generateTransactionId($cart->id)
        );

        if (! $session) {
            session()->flash('error', trans('sslcommerz::app.response.payment-failed'));

            return redirect()->route('shop.checkout.cart.index');
        }

        return redirect()->away($session['GatewayPageURL']);
    }

    /**
     * Receive the customer back from SSLCommerz, whether they paid, failed or cancelled.
     *
     * SSLCommerz posts the outcome here from its own domain, and a browser does not send the
     * session cookie on a cross site POST under the `lax` policy Laravel defaults to, so this
     * route runs without the session middleware: starting a session here would hand the browser
     * a new empty one and sign the customer out of the storefront. The order is placed at this
     * point because this is the last time the outcome is still covered by SSLCommerz's own
     * signature, and the customer is then sent on to a GET route, where their session is back.
     */
    public function callback(): RedirectResponse
    {
        $payload = request()->post();

        $status = SSLCommerzPaymentStatus::tryFrom(strtoupper((string) ($payload['status'] ?? '')));

        if ($status?->isSuccessful()) {
            try {
                $this->settle($payload);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->to(URL::temporarySignedRoute('sslcommerz.complete', now()->addHour(), array_filter([
            'tran_id' => $payload['tran_id'] ?? null,
            'status' => $status?->value,
        ])));
    }

    /**
     * Show the customer what became of the payment settled in the callback.
     *
     * The link is signed by the callback, so a reference typed by hand is refused rather than
     * looked up, and even a signed one only ever finds an order, never places one.
     */
    public function complete(): RedirectResponse
    {
        if (! request()->hasValidSignature()) {
            session()->flash('error', trans('sslcommerz::app.response.payment-failed'));

            return redirect()->route('shop.checkout.cart.index');
        }

        $tranId = (string) request()->query('tran_id');

        $cartId = $this->sslCommerz->parseCartId($tranId);

        if (
            $cartId
            && $order = $this->findOrder($cartId)
        ) {
            session()->flash('order_id', $order->id);

            session()->flash('success', trans('sslcommerz::app.response.payment-success'));

            return redirect()->route('shop.checkout.onepage.success');
        }

        $status = SSLCommerzPaymentStatus::tryFrom((string) request()->query('status'));

        if ($status?->isSuccessful()) {
            session()->flash('error', trans('sslcommerz::app.response.verification-failed', ['tranId' => $tranId]));
        } elseif ($status?->isCancelled()) {
            session()->flash('warning', trans('sslcommerz::app.response.payment-cancelled'));
        } else {
            session()->flash('error', trans('sslcommerz::app.response.payment-failed'));
        }

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Handle SSLCommerz's server to server notification of a payment.
     *
     * This is what places the order when the customer paid but never made it back to the store,
     * having closed the tab or lost their connection on the way.
     */
    public function ipn(): JsonResponse
    {
        try {
            $payload = request()->post();

            $status = SSLCommerzPaymentStatus::tryFrom(strtoupper((string) ($payload['status'] ?? '')));

            if (! $status?->isSuccessful()) {
                return response()->json(['status' => 'ignored']);
            }

            if (! $this->sslCommerz->verifySignature($payload)) {
                return response()->json(['status' => 'signature_mismatch']);
            }

            $cartId = $this->sslCommerz->parseCartId($payload['tran_id'] ?? null);

            if (
                $cartId
                && $this->findOrder($cartId)
            ) {
                return response()->json(['status' => 'order_already_exists']);
            }

            if (! $this->settle($payload)) {
                return response()->json(['status' => 'payment_not_confirmed']);
            }

            return response()->json(['status' => 'order_created']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Confirm a payment with SSLCommerz and turn it into an order.
     *
     * What is posted only says which payment to look at. Whether it was taken, and for how much,
     * is read back from the validation API with the store's own credentials, so a replayed or
     * forged post cannot settle anything SSLCommerz itself would not.
     */
    protected function settle(array $payload): ?OrderContract
    {
        if (! $this->sslCommerz->verifySignature($payload)) {
            return null;
        }

        $cartId = $this->sslCommerz->parseCartId($payload['tran_id'] ?? null);

        if (! $cartId) {
            return null;
        }

        $validation = $this->sslCommerz->validateTransaction($payload['val_id'] ?? null);

        $status = SSLCommerzPaymentStatus::tryFrom(strtoupper((string) ($validation['status'] ?? '')));

        if (
            ! $status?->isSuccessful()
            || ($validation['tran_id'] ?? null) !== ($payload['tran_id'] ?? null)
        ) {
            return null;
        }

        return $this->placeOrder($cartId, $validation);
    }

    /**
     * Find the order already placed for a cart, if there is one.
     */
    protected function findOrder(int $cartId): ?OrderContract
    {
        return $this->orderRepository->findOneWhere(['cart_id' => $cartId]);
    }

    /**
     * Turn a confirmed payment into an order.
     *
     * The customer's return and the IPN usually arrive together, so the work runs under a lock
     * and looks for an order again once inside it. A payment SSLCommerz flags as risky still
     * becomes an order, because the money has been taken, but it is left pending and uninvoiced
     * so that the customer can be verified before anything ships.
     */
    protected function placeOrder(int $cartId, array $validation): ?OrderContract
    {
        return Cache::lock('sslcommerz.order.'.$cartId, 30)->block(10, function () use ($cartId, $validation) {
            if ($order = $this->findOrder($cartId)) {
                return $order;
            }

            $cart = $this->cartRepository->find($cartId);

            if (
                ! $cart
                || ! $cart->is_active
            ) {
                return null;
            }

            Cart::setCart($cart);

            core()->setCurrentCurrency($cart->cart_currency_code);

            Cart::collectTotals();

            $cart = Cart::getCart();

            if (! $cart) {
                return null;
            }

            if (! $this->amountMatches($cart, $validation)) {
                logger()->warning('SSLCommerz took a payment that does not match the cart it was taken for.', [
                    'cart_id' => $cartId,
                    'tran_id' => $validation['tran_id'] ?? null,
                    'val_id' => $validation['val_id'] ?? null,
                ]);

                return null;
            }

            $data = (new OrderResource($cart))->jsonSerialize();

            $data['payment']['additional'] = [
                'sslcommerz_tran_id' => $validation['tran_id'] ?? null,
                'sslcommerz_val_id' => $validation['val_id'] ?? null,
                'sslcommerz_bank_tran_id' => $validation['bank_tran_id'] ?? null,
                'sslcommerz_card_type' => $validation['card_type'] ?? null,
                'sslcommerz_risk_level' => $validation['risk_level'] ?? null,
                'sslcommerz_risk_title' => $validation['risk_title'] ?? null,
            ];

            $order = $this->orderRepository->create($data);

            if (! $this->sslCommerz->isRisky($validation)) {
                $this->orderRepository->update(['status' => Order::STATUS_PROCESSING], $order->id);

                if ($order->canInvoice()) {
                    $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));

                    $this->orderTransactionRepository->create([
                        'transaction_id' => $validation['tran_id'] ?? null,
                        'status' => $validation['status'] ?? null,
                        'type' => $order->payment->method,
                        'payment_method' => $order->payment->method,
                        'order_id' => $order->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $order->base_grand_total,
                        'data' => json_encode($validation),
                    ]);
                }
            }

            Cart::deActivateCart();

            return $order;
        });
    }

    /**
     * Check that the cart still totals what SSLCommerz reports having taken.
     *
     * Measured against the base total in the currency the payment was started for, which is
     * what SSLCommerz echoes back as the transaction currency and amount.
     */
    protected function amountMatches($cart, array $validation): bool
    {
        $amount = $this->sslCommerz->getPaidAmount($validation);

        if ($amount === null) {
            return false;
        }

        return $this->sslCommerz->getPaidCurrency($validation) === $this->sslCommerz->getCurrency($cart)
            && round($amount, 2) === round((float) $cart->base_grand_total, 2);
    }

    /**
     * Prepare invoice data.
     */
    protected function prepareInvoiceData(OrderContract $order): array
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
