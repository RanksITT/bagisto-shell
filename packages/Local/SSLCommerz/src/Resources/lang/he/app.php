<?php

return [
    'description' => 'שלמו באמצעות bKash, Nagad, Rocket, כרטיס או בנקאות מקוונת דרך SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'קבלו תשלומים בכרטיסים, בבנקאות סלולרית ובבנקאות מקוונת דרך SSLCommerz.',
            'sandbox-info' => 'שליחת תשלומים לסביבת הבדיקות של SSLCommerz. לחנות בדיקות יש מזהה חנות וסיסמה משלה.',
            'store-id' => 'מזהה חנות',
            'store-id-info' => 'מזהה החנות ש-SSLCommerz הנפיקה עבור חנות זו.',
            'store-password' => 'סיסמת חנות',
            'store-password-info' => 'סיסמת ה-API שהונפקה יחד עם מזהה החנות, ולא הסיסמה שבה אתם מתחברים לפאנל הסוחר.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz מקבלת תשלומים בין :min לבין :max. סכום הזמנה זו הוא :amount.',
        'cart-not-found' => 'העגלה לא נמצאה או שאינה תקינה.',
        'payment-cancelled' => 'התשלום בוטל.',
        'payment-failed' => 'התשלום נכשל. אנא נסו שוב.',
        'payment-success' => 'התשלום הושלם בהצלחה.',
        'provide-credentials' => 'אנא ספקו פרטי גישה תקינים ל-SSLCommerz.',
        'session-refused' => 'SSLCommerz לא הצליח להתחיל את התשלום: :reason',
        'supported-currency-error' => 'המטבע :currency אינו נתמך. מטבעות נתמכים: :supportedCurrencies.',
        'verification-failed' => 'לא ניתן היה לאשר את התשלום. אם חויבתם, אנא צרו איתנו קשר וציינו את מספר העסקה :tranId.',
    ],
];
