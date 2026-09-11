<?php

return [
    'description' => '通过 SSLCommerz 使用 bKash、Nagad、Rocket、银行卡或网上银行付款。',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => '通过 SSLCommerz 接受银行卡、移动银行和网上银行付款。',
            'sandbox-info' => '将付款发送到 SSLCommerz 沙箱环境。沙箱商店有独立的商店 ID 和商店密码。',
            'store-id' => '商店 ID',
            'store-id-info' => 'SSLCommerz 为此商店签发的商店 ID。',
            'store-password' => '商店密码',
            'store-password-info' => '与商店 ID 一同签发的 API 密码，而不是登录商户后台的密码。',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz 接受 :min 至 :max 之间的付款。此订单总额为 :amount。',
        'cart-not-found' => '购物车不存在或无效。',
        'payment-cancelled' => '付款已取消。',
        'payment-failed' => '付款失败，请重试。',
        'payment-success' => '付款已成功完成。',
        'provide-credentials' => '请提供有效的 SSLCommerz 凭据。',
        'session-refused' => 'SSLCommerz 无法发起付款：:reason',
        'supported-currency-error' => '不支持货币 :currency。支持的货币：:supportedCurrencies。',
        'verification-failed' => '无法确认您的付款。如果已被扣款，请联系我们并提供交易号 :tranId。',
    ],
];
