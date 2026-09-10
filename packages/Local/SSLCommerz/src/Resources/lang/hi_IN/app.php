<?php

return [
    'description' => 'SSLCommerz के माध्यम से bKash, Nagad, Rocket, कार्ड या इंटरनेट बैंकिंग से भुगतान करें।',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'SSLCommerz के माध्यम से कार्ड, मोबाइल बैंकिंग और इंटरनेट बैंकिंग भुगतान स्वीकार करें।',
            'sandbox-info' => 'भुगतान SSLCommerz सैंडबॉक्स में भेजें। सैंडबॉक्स स्टोर की अपनी अलग स्टोर आईडी और स्टोर पासवर्ड होते हैं।',
            'store-id' => 'स्टोर आईडी',
            'store-id-info' => 'इस स्टोर के लिए SSLCommerz द्वारा जारी की गई स्टोर आईडी।',
            'store-password' => 'स्टोर पासवर्ड',
            'store-password-info' => 'स्टोर आईडी के साथ जारी किया गया API पासवर्ड, न कि मर्चेंट पैनल में लॉग इन करने का पासवर्ड।',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz :min से :max तक के भुगतान स्वीकार करता है। इस ऑर्डर की कुल राशि :amount है।',
        'cart-not-found' => 'कार्ट नहीं मिला या अमान्य है।',
        'payment-cancelled' => 'भुगतान रद्द कर दिया गया।',
        'payment-failed' => 'भुगतान विफल रहा। कृपया पुनः प्रयास करें।',
        'payment-success' => 'भुगतान सफलतापूर्वक पूरा हुआ।',
        'provide-credentials' => 'कृपया मान्य SSLCommerz क्रेडेंशियल प्रदान करें।',
        'supported-currency-error' => 'मुद्रा :currency समर्थित नहीं है। समर्थित मुद्राएँ: :supportedCurrencies.',
        'verification-failed' => 'आपके भुगतान की पुष्टि नहीं हो सकी। यदि आपसे राशि ली गई है, तो कृपया लेनदेन :tranId का उल्लेख करते हुए हमसे संपर्क करें।',
    ],
];
