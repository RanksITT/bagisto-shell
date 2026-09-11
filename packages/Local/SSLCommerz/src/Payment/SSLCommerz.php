<?php

namespace Local\SSLCommerz\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Payment\Payment\Payment;

class SSLCommerz extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'sslcommerz';

    /**
     * SSLCommerz's sandbox host.
     */
    const SANDBOX_URL = 'https://sandbox.sslcommerz.com';

    /**
     * SSLCommerz's production host.
     */
    const PRODUCTION_URL = 'https://securepay.sslcommerz.com';

    /**
     * The endpoint a hosted checkout session is opened at.
     */
    const SESSION_ENDPOINT = '/gwprocess/v4/api.php';

    /**
     * The endpoint a payment is confirmed at, with the store's own credentials.
     */
    const VALIDATION_ENDPOINT = '/validator/api/validationserverAPI.php';

    /**
     * The smallest payment SSLCommerz accepts, in BDT.
     */
    const MINIMUM_AMOUNT = 10;

    /**
     * The largest payment SSLCommerz accepts, in BDT.
     */
    const MAXIMUM_AMOUNT = 500000;

    /**
     * Currencies SSLCommerz settles in.
     *
     * Anything but BDT is converted to BDT at SSLCommerz's rate on the day, and every one of
     * these is charged with two decimal places, which is how the amount is sent.
     *
     * @var string[]
     */
    protected $supportedCurrencies = ['BDT', 'USD', 'EUR', 'SGD', 'INR', 'MYR'];

    /**
     * Get redirect URL for SSLCommerz payment.
     *
     * Returning a non-null URL is what makes the checkout hand control to this package:
     * the core one page checkout skips order creation and sends the customer here instead.
     */
    public function getRedirectUrl(): string
    {
        return route('sslcommerz.redirect');
    }

    /**
     * Check if payment method is available.
     */
    public function isAvailable(): bool
    {
        return parent::isAvailable() && $this->hasValidCredentials();
    }

    /**
     * Get payment method title.
     */
    public function getTitle(): string
    {
        return $this->getConfigData('title') ?? trans('sslcommerz::app.title');
    }

    /**
     * Get payment method description.
     */
    public function getDescription(): string
    {
        return $this->getConfigData('description') ?? trans('sslcommerz::app.description');
    }

    /**
     * Get payment method image.
     *
     * The bundled logo is inlined rather than linked, because this package ships outside the
     * shop theme and so has nothing in the theme's build to point at.
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        if ($url) {
            return Storage::url($url);
        }

        return 'data:image/png;base64,'.base64_encode(
            (string) file_get_contents(dirname(__DIR__).'/Resources/assets/images/sslcommerz.png')
        );
    }

    /**
     * Get the Store ID SSLCommerz issued for this store.
     */
    public function getStoreId(): ?string
    {
        return $this->getConfigData('store_id');
    }

    /**
     * Get the store password SSLCommerz issued with the Store ID.
     */
    public function getStorePassword(): ?string
    {
        return $this->getConfigData('store_password');
    }

    /**
     * Check if sandbox mode is enabled.
     */
    public function isSandbox(): bool
    {
        return (bool) $this->getConfigData('sandbox');
    }

    /**
     * Get the SSLCommerz host for the configured environment.
     */
    public function getBaseUrl(): string
    {
        return $this->isSandbox()
            ? self::SANDBOX_URL
            : self::PRODUCTION_URL;
    }

    /**
     * Check if all required credentials are configured.
     */
    public function hasValidCredentials(): bool
    {
        return ! empty($this->getStoreId()) && ! empty($this->getStorePassword());
    }

    /**
     * Get the currencies SSLCommerz settles in.
     *
     * @return string[]
     */
    public function getSupportedCurrencies(): array
    {
        return $this->supportedCurrencies;
    }

    /**
     * Check whether SSLCommerz settles in the given currency.
     */
    public function isCurrencySupported(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->supportedCurrencies, true);
    }

    /**
     * Get the currency the payment is charged in.
     */
    public function getCurrency($cart): string
    {
        return strtoupper($cart->base_currency_code ?: core()->getBaseCurrencyCode());
    }

    /**
     * Check whether SSLCommerz will take a payment of this size.
     *
     * The limits are quoted in BDT, and any other currency is converted at a rate only
     * SSLCommerz knows, so only a BDT amount can be measured against them here.
     */
    public function isAmountAccepted(float $amount, string $currency): bool
    {
        if (strtoupper($currency) !== 'BDT') {
            return true;
        }

        return $amount >= self::MINIMUM_AMOUNT
            && $amount <= self::MAXIMUM_AMOUNT;
    }

    /**
     * Generate a transaction id.
     */
    public function generateTransactionId(int $cartId): string
    {
        return 'SSL'.$cartId.'T'.Str::upper(Str::random(10));
    }

    /**
     * Read the cart back out of a transaction id.
     *
     * The cart is carried in the reference SSLCommerz echoes back, which is how the other
     * gateways find their way back to a cart without keeping a table of their own.
     */
    public function parseCartId(?string $tranId): ?int
    {
        if (! preg_match('/^SSL(\d+)T/', (string) $tranId, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Open a hosted checkout session.
     *
     * SSLCommerz's answer is handed back whether it opened the session or refused it, so that the
     * reason for a refusal can be shown to the customer. Nothing comes back only when SSLCommerz
     * could not be reached or did not answer in JSON.
     */
    public function initiatePayment($cart, string $tranId): ?array
    {
        try {
            $response = Http::asForm()->post(
                $this->getBaseUrl().self::SESSION_ENDPOINT,
                $this->prepareSessionData($cart, $tranId)
            );

            if ($response->failed()) {
                return null;
            }

            $session = $response->json();

            if (! is_array($session)) {
                return null;
            }

            if (! $this->getGatewayPageUrl($session)) {
                logger()->error('SSLCommerz refused to open a payment session.', [
                    'tran_id' => $tranId,
                    'reason' => $this->getFailureReason($session),
                ]);
            }

            return $session;
        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Get the hosted checkout page an opened session sends the customer to.
     */
    public function getGatewayPageUrl(?array $session): ?string
    {
        if (
            strtoupper((string) ($session['status'] ?? '')) !== 'SUCCESS'
            || empty($session['GatewayPageURL'])
        ) {
            return null;
        }

        return (string) $session['GatewayPageURL'];
    }

    /**
     * Get the reason SSLCommerz gave for refusing to open a session.
     */
    public function getFailureReason(?array $session): ?string
    {
        $reason = trim((string) ($session['failedreason'] ?? ''));

        if ($reason === '') {
            return null;
        }

        return Str::limit($reason, 200);
    }

    /**
     * Ask SSLCommerz what became of a payment.
     *
     * This is the only word taken for whether a payment happened: it is answered to the store's
     * own credentials, so it cannot be forged by whoever posted the validation id.
     */
    public function validateTransaction(?string $valId): ?array
    {
        if (empty($valId)) {
            return null;
        }

        try {
            $response = Http::acceptJson()->get($this->getBaseUrl().self::VALIDATION_ENDPOINT, [
                'val_id' => $valId,
                'store_id' => $this->getStoreId(),
                'store_passwd' => $this->getStorePassword(),
                'format' => 'json',
            ]);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            report($e);

            return null;
        }
    }

    /**
     * Check the signature SSLCommerz posts its outcome with.
     *
     * `verify_key` names the fields that were signed, in the order they were signed; each is
     * paired with its posted value, the store password is added as an md5 of itself, and the
     * pairs are sorted by name and joined before being hashed. This is the procedure SSLCommerz
     * publishes in its own PHP library.
     */
    public function verifySignature(array $payload): bool
    {
        if (
            empty($payload['verify_sign'])
            || empty($payload['verify_key'])
        ) {
            return false;
        }

        $signed = [];

        foreach (explode(',', (string) $payload['verify_key']) as $field) {
            $signed[$field] = $payload[$field] ?? '';
        }

        $signed['store_passwd'] = md5((string) $this->getStorePassword());

        ksort($signed);

        $hashables = [];

        foreach ($signed as $field => $value) {
            $hashables[] = $field.'='.$value;
        }

        return hash_equals(md5(implode('&', $hashables)), (string) $payload['verify_sign']);
    }

    /**
     * Read the amount SSLCommerz reports having taken, in the currency it was asked for.
     */
    public function getPaidAmount(?array $validation): ?float
    {
        foreach (['currency_amount', 'amount'] as $key) {
            if (is_numeric($validation[$key] ?? null)) {
                return (float) $validation[$key];
            }
        }

        return null;
    }

    /**
     * Read the currency SSLCommerz reports having charged in.
     */
    public function getPaidCurrency(?array $validation): ?string
    {
        foreach (['currency_type', 'currency'] as $key) {
            if (! empty($validation[$key])) {
                return strtoupper((string) $validation[$key]);
            }
        }

        return null;
    }

    /**
     * Check whether SSLCommerz flagged the payment as one to verify before shipping.
     */
    public function isRisky(?array $validation): bool
    {
        return (int) ($validation['risk_level'] ?? 0) === 1;
    }

    /**
     * Prepare the session SSLCommerz is asked to open.
     */
    protected function prepareSessionData($cart, string $tranId): array
    {
        $address = $cart->billing_address;

        return array_filter(array_merge([
            'store_id' => $this->getStoreId(),
            'store_passwd' => $this->getStorePassword(),
            'total_amount' => $this->formatAmount((float) $cart->base_grand_total),
            'currency' => $this->getCurrency($cart),
            'tran_id' => $tranId,
            'success_url' => route('sslcommerz.callback'),
            'fail_url' => route('sslcommerz.callback'),
            'cancel_url' => route('sslcommerz.callback'),
            'ipn_url' => route('sslcommerz.ipn'),
            'product_name' => $this->limit($cart->items->pluck('name')->implode(', '), 255),
            'product_category' => 'general',
            'product_profile' => 'physical-goods',
            'num_of_item' => (int) $cart->items_qty,
            'cus_name' => $this->limit($this->getCustomerName($cart), 50),
            'cus_email' => $this->limit($address?->email ?: $cart->customer_email, 50),
            'cus_phone' => $this->limit($address?->phone, 20),
            'cus_add1' => $this->limit($address?->address, 50),
            'cus_city' => $this->limit($address?->city, 50),
            'cus_state' => $this->limit($address?->state, 50),
            'cus_postcode' => $this->limit($address?->postcode, 30),
            'cus_country' => $this->limit($this->getCountryName($address), 50),
            'value_a' => $cart->id,
        ], $this->prepareShippingData($cart)), fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Prepare the shipping details sent along with the payment.
     */
    protected function prepareShippingData($cart): array
    {
        $address = $cart->shipping_address;

        if (! $address) {
            return ['shipping_method' => 'NO'];
        }

        return [
            'shipping_method' => 'Courier',
            'ship_name' => $this->limit(trim($address->first_name.' '.$address->last_name), 50),
            'ship_add1' => $this->limit($address->address, 50),
            'ship_city' => $this->limit($address->city, 50),
            'ship_state' => $this->limit($address->state, 50),
            'ship_postcode' => $this->limit($address->postcode, 50),
            'ship_country' => $this->limit($this->getCountryName($address), 50),
        ];
    }

    /**
     * Get the name the payment is taken in.
     */
    protected function getCustomerName($cart): string
    {
        $address = $cart->billing_address;

        return trim($address?->first_name.' '.$address?->last_name)
            ?: trim($cart->customer_first_name.' '.$cart->customer_last_name);
    }

    /**
     * Get the full country name of an address, which is what SSLCommerz expects.
     */
    protected function getCountryName($address): ?string
    {
        if (empty($address?->country)) {
            return null;
        }

        return core()->country_name($address->country);
    }

    /**
     * Format an amount the way SSLCommerz expects it.
     */
    protected function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Cut a value down to the length SSLCommerz accepts for its field.
     */
    protected function limit(?string $value, int $length): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_substr(trim($value), 0, $length);
    }
}
