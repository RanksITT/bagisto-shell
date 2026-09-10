<?php

return [
    'description' => 'Оплачивайте через bKash, Nagad, Rocket, картой или через интернет-банк с помощью SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Принимайте платежи картами, через мобильный и интернет-банкинг с помощью SSLCommerz.',
            'sandbox-info' => 'Отправлять платежи в тестовую среду SSLCommerz. У тестового магазина собственные ID и пароль.',
            'store-id' => 'ID магазина',
            'store-id-info' => 'ID магазина, выданный SSLCommerz для этого магазина.',
            'store-password' => 'Пароль магазина',
            'store-password-info' => 'Пароль API, выданный вместе с ID магазина, а не пароль для входа в панель продавца.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz принимает платежи от :min до :max. Сумма этого заказа — :amount.',
        'cart-not-found' => 'Корзина не найдена или недействительна.',
        'payment-cancelled' => 'Платёж отменён.',
        'payment-failed' => 'Платёж не прошёл. Пожалуйста, попробуйте ещё раз.',
        'payment-success' => 'Платёж успешно завершён.',
        'provide-credentials' => 'Укажите действительные учётные данные SSLCommerz.',
        'supported-currency-error' => 'Валюта :currency не поддерживается. Поддерживаемые валюты: :supportedCurrencies.',
        'verification-failed' => 'Не удалось подтвердить ваш платёж. Если деньги были списаны, свяжитесь с нами и укажите номер транзакции :tranId.',
    ],
];
