<?php

return [
    'id' => env('ANTILOPAY_ID'),
    'secret_id' => env('ANTILOPAY_SECRET_ID'),
    'secret_key' => env('ANTILOPAY_SECRET_KEY'),
    'callback_key' => env('ANTILOPAY_CALLBACK_KEY'),
    'uri' => 'https://lk.antilopay.com/api/v1/payment/',
    'api_version' => 1,
    'payment_id' => [
        20212 => 'SBP', // СБП
        20216 => 'SBER_PAY', // СберПей
        20301 => 'CARD_RU'  // Карты
    ]
];
