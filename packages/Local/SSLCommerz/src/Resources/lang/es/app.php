<?php

return [
    'description' => 'Paga con bKash, Nagad, Rocket, tarjetas o banca en línea a través de SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Acepta pagos con tarjeta, banca móvil y banca en línea a través de SSLCommerz.',
            'sandbox-info' => 'Envía los pagos al entorno de pruebas de SSLCommerz. Una tienda de pruebas tiene su propio ID de tienda y contraseña.',
            'store-id' => 'ID de tienda',
            'store-id-info' => 'El ID de tienda que SSLCommerz emitió para esta tienda.',
            'store-password' => 'Contraseña de la tienda',
            'store-password-info' => 'La contraseña de API emitida junto con el ID de tienda, no la contraseña con la que inicias sesión en el panel de comerciante.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'SSLCommerz acepta pagos entre :min y :max. Este pedido suma :amount.',
        'cart-not-found' => 'Carrito no encontrado o no válido.',
        'payment-cancelled' => 'El pago fue cancelado.',
        'payment-failed' => 'El pago falló. Por favor, inténtalo de nuevo.',
        'payment-success' => 'El pago se completó correctamente.',
        'provide-credentials' => 'Proporciona credenciales válidas de SSLCommerz.',
        'supported-currency-error' => 'La moneda :currency no es compatible. Monedas compatibles: :supportedCurrencies.',
        'verification-failed' => 'No se pudo confirmar tu pago. Si se te cobró, contáctanos indicando la transacción :tranId.',
    ],
];
