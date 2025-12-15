<?php

return [
    'client_id' => env('PAYOS_CLIENT_ID'),
    'api_key' => env('PAYOS_API_KEY'),
    'checksum_key' => env('PAYOS_CHECKSUM_KEY'),
    'partner_code' => env('PAYOS_PARTNER_CODE'),
    'return_url' => env('PAYOS_RETURN_URL', env('APP_URL') . '/payos/return'),
    'cancel_url' => env('PAYOS_CANCEL_URL', env('APP_URL') . '/payos/cancel'),
    'standard_amount' => (int) env('PAYOS_STANDARD_AMOUNT', 100000),
];
