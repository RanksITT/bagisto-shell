<?php

return [
    'description' => 'ادفع باستخدام bKash أو Nagad أو Rocket أو البطاقات أو الخدمات المصرفية عبر الإنترنت من خلال SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'اقبل المدفوعات بالبطاقات والخدمات المصرفية عبر الجوال والإنترنت من خلال SSLCommerz.',
            'sandbox-info' => 'إرسال المدفوعات إلى بيئة الاختبار في SSLCommerz. لمتجر الاختبار معرّف متجر وكلمة مرور خاصان به.',
            'store-id' => 'معرّف المتجر',
            'store-id-info' => 'معرّف المتجر الذي أصدرته SSLCommerz لهذا المتجر.',
            'store-password' => 'كلمة مرور المتجر',
            'store-password-info' => 'كلمة مرور واجهة API الصادرة مع معرّف المتجر، وليست كلمة المرور التي تسجّل بها الدخول إلى لوحة التاجر.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'تقبل SSLCommerz المدفوعات بين :min و :max. إجمالي هذا الطلب :amount.',
        'cart-not-found' => 'لم يتم العثور على السلة أو أنها غير صالحة.',
        'payment-cancelled' => 'تم إلغاء الدفع.',
        'payment-failed' => 'فشل الدفع. يرجى المحاولة مرة أخرى.',
        'payment-success' => 'تم الدفع بنجاح.',
        'provide-credentials' => 'يرجى تقديم بيانات اعتماد SSLCommerz صالحة.',
        'supported-currency-error' => 'العملة :currency غير مدعومة. العملات المدعومة: :supportedCurrencies.',
        'verification-failed' => 'تعذّر تأكيد دفعتك. إذا تم خصم المبلغ منك، يرجى التواصل معنا وذكر رقم المعاملة :tranId.',
    ],
];
