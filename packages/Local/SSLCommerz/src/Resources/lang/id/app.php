<?php

return [
    'description' => 'Bayar dengan bKash, Nagad, Rocket, kartu, atau internet banking melalui SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Terima pembayaran kartu, mobile banking, dan internet banking melalui SSLCommerz.',
            'sandbox-info' => 'Kirim pembayaran ke sandbox SSLCommerz. Toko sandbox memiliki ID Toko dan Kata Sandi Toko tersendiri.',
            'store-id' => 'ID Toko',
            'store-id-info' => 'ID Toko yang diterbitkan SSLCommerz untuk toko ini.',
            'store-password' => 'Kata Sandi Toko',
            'store-password-info' => 'Kata sandi API yang diterbitkan bersama ID Toko, bukan kata sandi untuk masuk ke panel merchant.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz menerima pembayaran antara :min dan :max. Total pesanan ini :amount.',
        'cart-not-found' => 'Keranjang tidak ditemukan atau tidak valid.',
        'payment-cancelled' => 'Pembayaran dibatalkan.',
        'payment-failed' => 'Pembayaran gagal. Silakan coba lagi.',
        'payment-success' => 'Pembayaran berhasil diselesaikan.',
        'provide-credentials' => 'Harap berikan kredensial SSLCommerz yang valid.',
        'supported-currency-error' => 'Mata uang :currency tidak didukung. Mata uang yang didukung: :supportedCurrencies.',
        'verification-failed' => 'Pembayaran Anda tidak dapat dikonfirmasi. Jika Anda telah ditagih, silakan hubungi kami dengan menyebutkan transaksi :tranId.',
    ],
];
