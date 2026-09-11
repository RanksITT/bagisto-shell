<?php

return [
    'description' => 'Paga con bKash, Nagad, Rocket, carta o home banking tramite SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Accetta pagamenti con carta, mobile banking e home banking tramite SSLCommerz.',
            'sandbox-info' => 'Invia i pagamenti all\'ambiente di test di SSLCommerz. Un negozio di test ha un proprio ID negozio e una propria password.',
            'store-id' => 'ID negozio',
            'store-id-info' => 'L\'ID negozio rilasciato da SSLCommerz per questo negozio.',
            'store-password' => 'Password negozio',
            'store-password-info' => 'La password API rilasciata con l\'ID negozio, non la password con cui accedi al pannello esercente.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz accetta pagamenti tra :min e :max. Questo ordine ammonta a :amount.',
        'cart-not-found' => 'Carrello non trovato o non valido.',
        'payment-cancelled' => 'Il pagamento è stato annullato.',
        'payment-failed' => 'Il pagamento non è andato a buon fine. Riprova.',
        'payment-success' => 'Il pagamento è stato completato con successo.',
        'provide-credentials' => 'Fornisci credenziali SSLCommerz valide.',
        'session-refused' => 'SSLCommerz non è riuscito ad avviare il pagamento: :reason',
        'supported-currency-error' => 'La valuta :currency non è supportata. Valute supportate: :supportedCurrencies.',
        'verification-failed' => 'Non è stato possibile confermare il pagamento. Se ti è stato addebitato, contattaci indicando la transazione :tranId.',
    ],
];
