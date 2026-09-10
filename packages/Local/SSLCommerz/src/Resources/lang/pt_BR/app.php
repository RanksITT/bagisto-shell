<?php

return [
    'description' => 'Pague com bKash, Nagad, Rocket, cartão ou internet banking pelo SSLCommerz.',
    'title' => 'SSLCommerz',

    'admin' => [
        'system' => [
            'info' => 'Aceite pagamentos com cartão, mobile banking e internet banking pelo SSLCommerz.',
            'sandbox-info' => 'Enviar os pagamentos para o ambiente de testes do SSLCommerz. Uma loja de testes tem ID e senha próprios.',
            'store-id' => 'ID da loja',
            'store-id-info' => 'O ID da loja emitido pelo SSLCommerz para esta loja.',
            'store-password' => 'Senha da loja',
            'store-password-info' => 'A senha de API emitida com o ID da loja, não a senha usada para entrar no painel do lojista.',
            'title' => 'SSLCommerz',
        ],
    ],

    'response' => [
        'amount-out-of-range' => 'O SSLCommerz aceita pagamentos entre :min e :max. Este pedido totaliza :amount.',
        'cart-not-found' => 'Carrinho não encontrado ou inválido.',
        'payment-cancelled' => 'O pagamento foi cancelado.',
        'payment-failed' => 'O pagamento falhou. Tente novamente.',
        'payment-success' => 'O pagamento foi concluído com sucesso.',
        'provide-credentials' => 'Informe credenciais válidas do SSLCommerz.',
        'supported-currency-error' => 'A moeda :currency não é suportada. Moedas suportadas: :supportedCurrencies.',
        'verification-failed' => 'Não foi possível confirmar seu pagamento. Se você foi cobrado, entre em contato conosco informando a transação :tranId.',
    ],
];
