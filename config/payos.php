<?php

return [
    'client_id' => env('PAYOS_CLIENT_ID'),
    'api_key' => env('PAYOS_API_KEY'),
    'checksum_key' => env('PAYOS_CHECKSUM_KEY'),
    'partner_code' => env('PAYOS_PARTNER_CODE'),
    'return_url' => env('PAYOS_RETURN_URL', env('APP_URL') . '/payos/return'),
    'cancel_url' => env('PAYOS_CANCEL_URL', env('APP_URL') . '/payos/cancel'),
    'standard_amount' => (int) env('PAYOS_STANDARD_AMOUNT', 100000),

    'plans' => [
        'vip_week' => [
            'key' => 'vip_week',
            'name' => 'Kỳ Thủ VIP (1 Tuần)',
            'amount' => (int) env('PAYOS_VIP_WEEK_AMOUNT', 19000),
            'days' => 7,
            'badge' => 'Trải nghiệm',
            'description' => 'VIP 1 Tuan',
        ],
        'vip_month' => [
            'key' => 'vip_month',
            'name' => 'Kỳ Thủ VIP (1 Tháng)',
            'amount' => (int) env('PAYOS_VIP_MONTH_AMOUNT', 49000),
            'days' => 30,
            'badge' => 'Phổ biến nhất',
            'popular' => true,
            'description' => 'VIP 1 Thang',
        ],
        'standard' => [
            'key' => 'standard',
            'name' => 'Kỳ Thủ Hoàng Gia (Vĩnh Viễn)',
            'amount' => (int) env('PAYOS_STANDARD_AMOUNT', 199000),
            'days' => null,
            'badge' => 'Tiết kiệm nhất',
            'description' => 'VIP Tron Doi',
        ],
    ],
];
