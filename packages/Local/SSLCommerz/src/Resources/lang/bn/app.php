<?php

return [
    'description' => 'SSLCommerz এর মাধ্যমে বিকাশ, নগদ, রকেট, কার্ড বা ইন্টারনেট ব্যাংকিং দিয়ে পেমেন্ট করুন।',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'SSLCommerz এর মাধ্যমে কার্ড, মোবাইল ব্যাংকিং ও ইন্টারনেট ব্যাংকিং পেমেন্ট গ্রহণ করুন।',
            'sandbox-info' => 'পেমেন্ট SSLCommerz স্যান্ডবক্সে পাঠান। স্যান্ডবক্স স্টোরের আলাদা স্টোর আইডি ও স্টোর পাসওয়ার্ড থাকে।',
            'store-id' => 'স্টোর আইডি',
            'store-id-info' => 'এই স্টোরের জন্য SSLCommerz যে স্টোর আইডি দিয়েছে।',
            'store-password' => 'স্টোর পাসওয়ার্ড',
            'store-password-info' => 'স্টোর আইডির সাথে দেওয়া API পাসওয়ার্ড, মার্চেন্ট প্যানেলে লগইনের পাসওয়ার্ড নয়।',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz :min থেকে :max পর্যন্ত পেমেন্ট গ্রহণ করে। এই অর্ডারের মোট :amount।',
        'cart-not-found' => 'কার্ট পাওয়া যায়নি বা অবৈধ।',
        'payment-cancelled' => 'পেমেন্ট বাতিল করা হয়েছে।',
        'payment-failed' => 'পেমেন্ট ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।',
        'payment-success' => 'পেমেন্ট সফলভাবে সম্পন্ন হয়েছে।',
        'provide-credentials' => 'অনুগ্রহ করে সঠিক SSLCommerz ক্রেডেনশিয়াল দিন।',
        'supported-currency-error' => 'মুদ্রা :currency সমর্থিত নয়। সমর্থিত মুদ্রাসমূহ: :supportedCurrencies।',
        'verification-failed' => 'আপনার পেমেন্ট নিশ্চিত করা যায়নি। টাকা কেটে থাকলে লেনদেন নম্বর :tranId উল্লেখ করে আমাদের সাথে যোগাযোগ করুন।',
    ],
];
