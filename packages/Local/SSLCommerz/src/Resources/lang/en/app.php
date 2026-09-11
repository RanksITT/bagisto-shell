<?php

return [
    'description' => 'Pay with bKash, Nagad, Rocket, cards or internet banking through SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Accept cards, mobile banking and internet banking through SSLCommerz.',
            'sandbox-info' => 'Send payments to the SSLCommerz sandbox. A sandbox store has its own Store ID and Store Password.',
            'store-id' => 'Store ID',
            'store-id-info' => 'The Store ID SSLCommerz issued for this store.',
            'store-password' => 'Store Password',
            'store-password-info' => 'The API password issued with the Store ID, not the password you sign in to the merchant panel with.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz accepts payments between :min and :max. This order totals :amount.',
        'cart-not-found' => 'Cart not found or invalid.',
        'payment-cancelled' => 'The payment was cancelled.',
        'payment-failed' => 'The payment failed. Please try again.',
        'payment-success' => 'The payment completed successfully.',
        'provide-credentials' => 'Please provide valid SSLCommerz credentials.',
        'session-refused' => 'SSLCommerz could not start the payment: :reason',
        'supported-currency-error' => 'The currency :currency is not supported. Supported Currencies: :supportedCurrencies.',
        'verification-failed' => 'Your payment could not be confirmed. If you were charged, please contact us and quote transaction :tranId.',
    ],
];
