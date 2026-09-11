<?php

return [
    'description' => 'Оплачуйте через bKash, Nagad, Rocket, карткою або через інтернет-банкінг за допомогою SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Приймайте платежі картками, через мобільний та інтернет-банкінг за допомогою SSLCommerz.',
            'sandbox-info' => 'Надсилати платежі до тестового середовища SSLCommerz. Тестовий магазин має власні ID і пароль.',
            'store-id' => 'ID магазину',
            'store-id-info' => 'ID магазину, виданий SSLCommerz для цього магазину.',
            'store-password' => 'Пароль магазину',
            'store-password-info' => 'Пароль API, виданий разом з ID магазину, а не пароль для входу в панель продавця.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz приймає платежі від :min до :max. Сума цього замовлення — :amount.',
        'cart-not-found' => 'Кошик не знайдено або він недійсний.',
        'payment-cancelled' => 'Платіж скасовано.',
        'payment-failed' => 'Платіж не вдався. Будь ласка, спробуйте ще раз.',
        'payment-success' => 'Платіж успішно завершено.',
        'provide-credentials' => 'Вкажіть дійсні облікові дані SSLCommerz.',
        'session-refused' => 'SSLCommerz не зміг розпочати оплату: :reason',
        'supported-currency-error' => 'Валюта :currency не підтримується. Підтримувані валюти: :supportedCurrencies.',
        'verification-failed' => 'Не вдалося підтвердити ваш платіж. Якщо кошти було списано, зв\'яжіться з нами та вкажіть номер транзакції :tranId.',
    ],
];
