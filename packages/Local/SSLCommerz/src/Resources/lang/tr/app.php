<?php

return [
    'description' => 'SSLCommerz üzerinden bKash, Nagad, Rocket, kart veya internet bankacılığı ile ödeyin.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'SSLCommerz üzerinden kart, mobil bankacılık ve internet bankacılığı ödemelerini kabul edin.',
            'sandbox-info' => 'Ödemeleri SSLCommerz test ortamına gönderin. Test mağazasının kendine ait Mağaza Kimliği ve Mağaza Şifresi vardır.',
            'store-id' => 'Mağaza Kimliği',
            'store-id-info' => 'SSLCommerz\'in bu mağaza için verdiği Mağaza Kimliği.',
            'store-password' => 'Mağaza Şifresi',
            'store-password-info' => 'Mağaza Kimliği ile birlikte verilen API şifresi; satıcı paneline giriş yaptığınız şifre değildir.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz :min ile :max arasındaki ödemeleri kabul eder. Bu siparişin toplamı :amount.',
        'cart-not-found' => 'Sepet bulunamadı veya geçersiz.',
        'payment-cancelled' => 'Ödeme iptal edildi.',
        'payment-failed' => 'Ödeme başarısız oldu. Lütfen tekrar deneyin.',
        'payment-success' => 'Ödeme başarıyla tamamlandı.',
        'provide-credentials' => 'Lütfen geçerli SSLCommerz kimlik bilgilerini girin.',
        'supported-currency-error' => ':currency para birimi desteklenmiyor. Desteklenen para birimleri: :supportedCurrencies.',
        'verification-failed' => 'Ödemeniz doğrulanamadı. Ücret alındıysa lütfen :tranId işlem numarasını belirterek bizimle iletişime geçin.',
    ],
];
