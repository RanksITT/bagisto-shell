<?php

return [
    'description' => 'با bKash، Nagad، Rocket، کارت یا بانکداری اینترنتی از طریق SSLCommerz پرداخت کنید.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'پرداخت با کارت، بانکداری موبایلی و بانکداری اینترنتی را از طریق SSLCommerz بپذیرید.',
            'sandbox-info' => 'پرداخت‌ها به محیط آزمایشی SSLCommerz ارسال شوند. فروشگاه آزمایشی شناسه و رمز عبور مخصوص خود را دارد.',
            'store-id' => 'شناسه فروشگاه',
            'store-id-info' => 'شناسه فروشگاهی که SSLCommerz برای این فروشگاه صادر کرده است.',
            'store-password' => 'رمز عبور فروشگاه',
            'store-password-info' => 'رمز عبور API که همراه شناسه فروشگاه صادر شده است، نه رمز ورود به پنل پذیرنده.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz پرداخت‌های بین :min و :max را می‌پذیرد. مبلغ این سفارش :amount است.',
        'cart-not-found' => 'سبد خرید یافت نشد یا نامعتبر است.',
        'payment-cancelled' => 'پرداخت لغو شد.',
        'payment-failed' => 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.',
        'payment-success' => 'پرداخت با موفقیت انجام شد.',
        'provide-credentials' => 'لطفاً اطلاعات احراز هویت معتبر SSLCommerz را وارد کنید.',
        'supported-currency-error' => 'ارز :currency پشتیبانی نمی‌شود. ارزهای پشتیبانی‌شده: :supportedCurrencies.',
        'verification-failed' => 'پرداخت شما تأیید نشد. اگر مبلغی از حساب شما کسر شده است، لطفاً با ذکر شماره تراکنش :tranId با ما تماس بگیرید.',
    ],
];
