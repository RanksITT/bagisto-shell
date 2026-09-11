<?php

return [
    'description' => 'Bezahlen Sie mit bKash, Nagad, Rocket, Karte oder Online-Banking über SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Akzeptieren Sie Zahlungen per Karte, Mobile Banking und Online-Banking über SSLCommerz.',
            'sandbox-info' => 'Zahlungen an die SSLCommerz-Sandbox senden. Ein Sandbox-Shop hat eine eigene Store-ID und ein eigenes Store-Passwort.',
            'store-id' => 'Store-ID',
            'store-id-info' => 'Die Store-ID, die SSLCommerz für diesen Shop ausgestellt hat.',
            'store-password' => 'Store-Passwort',
            'store-password-info' => 'Das mit der Store-ID ausgestellte API-Passwort, nicht das Passwort für die Anmeldung im Händlerportal.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz akzeptiert Zahlungen zwischen :min und :max. Diese Bestellung beträgt :amount.',
        'cart-not-found' => 'Warenkorb nicht gefunden oder ungültig.',
        'payment-cancelled' => 'Die Zahlung wurde abgebrochen.',
        'payment-failed' => 'Die Zahlung ist fehlgeschlagen. Bitte versuchen Sie es erneut.',
        'payment-success' => 'Die Zahlung wurde erfolgreich abgeschlossen.',
        'provide-credentials' => 'Bitte geben Sie gültige SSLCommerz-Zugangsdaten an.',
        'session-refused' => 'SSLCommerz konnte die Zahlung nicht starten: :reason',
        'supported-currency-error' => 'Die Währung :currency wird nicht unterstützt. Unterstützte Währungen: :supportedCurrencies.',
        'verification-failed' => 'Ihre Zahlung konnte nicht bestätigt werden. Falls Ihnen ein Betrag belastet wurde, kontaktieren Sie uns bitte unter Angabe der Transaktion :tranId.',
    ],
];
