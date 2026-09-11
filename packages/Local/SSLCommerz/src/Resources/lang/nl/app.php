<?php

return [
    'description' => 'Betaal met bKash, Nagad, Rocket, een kaart of internetbankieren via SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Accepteer betalingen met kaarten, mobiel bankieren en internetbankieren via SSLCommerz.',
            'sandbox-info' => 'Stuur betalingen naar de SSLCommerz-sandbox. Een sandboxwinkel heeft een eigen winkel-ID en wachtwoord.',
            'store-id' => 'Winkel-ID',
            'store-id-info' => 'De winkel-ID die SSLCommerz voor deze winkel heeft uitgegeven.',
            'store-password' => 'Winkelwachtwoord',
            'store-password-info' => 'Het API-wachtwoord dat samen met de winkel-ID is uitgegeven, niet het wachtwoord waarmee je inlogt op het merchantpaneel.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz accepteert betalingen tussen :min en :max. Deze bestelling bedraagt :amount.',
        'cart-not-found' => 'Winkelwagen niet gevonden of ongeldig.',
        'payment-cancelled' => 'De betaling is geannuleerd.',
        'payment-failed' => 'De betaling is mislukt. Probeer het opnieuw.',
        'payment-success' => 'De betaling is succesvol voltooid.',
        'provide-credentials' => 'Geef geldige SSLCommerz-inloggegevens op.',
        'session-refused' => 'SSLCommerz kon de betaling niet starten: :reason',
        'supported-currency-error' => 'De valuta :currency wordt niet ondersteund. Ondersteunde valuta: :supportedCurrencies.',
        'verification-failed' => 'Je betaling kon niet worden bevestigd. Als er een bedrag is afgeschreven, neem dan contact met ons op en vermeld transactie :tranId.',
    ],
];
