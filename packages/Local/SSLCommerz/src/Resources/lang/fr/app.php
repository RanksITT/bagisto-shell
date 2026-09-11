<?php

return [
    'description' => 'Payez avec bKash, Nagad, Rocket, une carte ou la banque en ligne via SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Acceptez les paiements par carte, banque mobile et banque en ligne via SSLCommerz.',
            'sandbox-info' => 'Envoyer les paiements vers l\'environnement de test SSLCommerz. Une boutique de test possède ses propres identifiant et mot de passe.',
            'store-id' => 'Identifiant de la boutique',
            'store-id-info' => 'L\'identifiant de boutique délivré par SSLCommerz pour cette boutique.',
            'store-password' => 'Mot de passe de la boutique',
            'store-password-info' => 'Le mot de passe API délivré avec l\'identifiant de boutique, et non le mot de passe de connexion au portail marchand.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz accepte les paiements entre :min et :max. Cette commande s\'élève à :amount.',
        'cart-not-found' => 'Panier introuvable ou invalide.',
        'payment-cancelled' => 'Le paiement a été annulé.',
        'payment-failed' => 'Le paiement a échoué. Veuillez réessayer.',
        'payment-success' => 'Le paiement a été effectué avec succès.',
        'provide-credentials' => 'Veuillez fournir des identifiants SSLCommerz valides.',
        'session-refused' => 'SSLCommerz n\'a pas pu lancer le paiement : :reason',
        'supported-currency-error' => 'La devise :currency n\'est pas prise en charge. Devises prises en charge : :supportedCurrencies.',
        'verification-failed' => 'Votre paiement n\'a pas pu être confirmé. Si vous avez été débité, contactez-nous en indiquant la transaction :tranId.',
    ],
];
