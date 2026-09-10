<?php

return [
    'description' => 'SSLCommerz を通じて bKash、Nagad、Rocket、カード、インターネットバンキングでお支払いいただけます。',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'SSLCommerz を通じてカード、モバイルバンキング、インターネットバンキングによる支払いを受け付けます。',
            'sandbox-info' => '支払いを SSLCommerz のサンドボックスに送信します。サンドボックスストアには専用のストア ID とストアパスワードがあります。',
            'store-id' => 'ストア ID',
            'store-id-info' => 'SSLCommerz がこのストア用に発行したストア ID です。',
            'store-password' => 'ストアパスワード',
            'store-password-info' => 'ストア ID と一緒に発行された API パスワードです。マーチャントパネルへのログインパスワードではありません。',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz で支払える金額は :min から :max までです。この注文の合計は :amount です。',
        'cart-not-found' => 'カートが見つからないか、無効です。',
        'payment-cancelled' => '支払いがキャンセルされました。',
        'payment-failed' => '支払いに失敗しました。もう一度お試しください。',
        'payment-success' => '支払いが正常に完了しました。',
        'provide-credentials' => '有効な SSLCommerz の認証情報を入力してください。',
        'supported-currency-error' => '通貨 :currency はサポートされていません。サポートされている通貨: :supportedCurrencies.',
        'verification-failed' => 'お支払いを確認できませんでした。請求が発生している場合は、取引番号 :tranId をお知らせのうえお問い合わせください。',
    ],
];
