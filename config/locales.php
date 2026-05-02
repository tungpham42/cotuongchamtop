<?php

return [
    'default' => 'vi',

    'supported' => ['vi', 'en', 'ja', 'ko', 'zh'],

    'labels' => [
        'vi' => 'Tiếng Việt',
        'en' => 'English',
        'ja' => '日本語',
        'ko' => '한국어',
        'zh' => '中文',
    ],

    'flags' => [
        'vi' => 'vn',
        'en' => 'us',
        'ja' => 'jp',
        'ko' => 'kr',
        'zh' => 'cn',
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy SEO paths
    |--------------------------------------------------------------------------
    |
    | These paths preserve the currently indexed public URLs. Refactors should
    | route these paths to shared controllers/views instead of changing them.
    |
    */
    'paths' => [
        'home' => [
            'vi' => '/',
            'en' => 'en',
            'ja' => 'ja',
            'ko' => 'ko',
            'zh' => 'zh',
        ],

        'room.list' => [
            'vi' => 'sanh-cho',
            'en' => 'rooms',
            'ja' => 'heya-ichiran',
            'ko' => 'bang-moglog',
            'zh' => 'fangjianliebiao',
        ],

        'room.host' => [
            'vi' => 'phong/{code}',
            'en' => 'room/{code}',
            'ja' => 'rumu/{code}',
            'ko' => 'bang/{code}',
            'zh' => 'fangjian/{code}',
        ],
        'room.guest' => [
            'vi' => 'phong/{code}/khach',
            'en' => 'room/{code}/guest',
            'ja' => 'rumu/{code}/geesuto',
            'ko' => 'bang/{code}/bangmun',
            'zh' => 'fangjian/{code}/zhuke',
        ],
        'room.random' => [
            'vi' => 'phong/{code}/ngau-nhien',
            'en' => 'room/{code}/random',
            'ja' => 'rumu/{code}/randamu',
            'ko' => 'bang/{code}/mujag-wiui',
            'zh' => 'fangjian/{code}/suijide',
        ],
        'room.watch' => [
            'vi' => 'phong/{code}/theo-doi',
            'en' => 'room/{code}/watch',
            'ja' => 'rumu/{code}/miru',
            'ko' => 'bang/{code}/boda',
            'zh' => 'fangjian/{code}/kan',
        ],
        'room.red' => [
            'vi' => 'phong/{code}/do',
            'en' => 'room/{code}/red',
            'ja' => 'rumu/{code}/aka',
            'ko' => 'bang/{code}/ppalgan',
            'zh' => 'fangjian/{code}/hongse',
        ],
        'room.black' => [
            'vi' => 'phong/{code}/den',
            'en' => 'room/{code}/black',
            'ja' => 'rumu/{code}/kuro',
            'ko' => 'bang/{code}/geom-eunsaeg',
            'zh' => 'fangjian/{code}/heise',
        ],

        'terms' => [
            'vi' => 'terms-and-conditions',
            'en' => 'terms-and-conditions',
            'ja' => 'terms-and-conditions',
            'ko' => 'terms-and-conditions',
            'zh' => 'terms-and-conditions',
        ],
        'privacy' => [
            'vi' => 'privacy-policy',
            'en' => 'privacy-policy',
            'ja' => 'privacy-policy',
            'ko' => 'privacy-policy',
            'zh' => 'privacy-policy',
        ],
        'about' => [
            'vi' => 'gioi-thieu',
            'en' => 'about-us',
            'ja' => 'yaku',
            'ko' => 'urie-daehae',
            'zh' => 'guanyuwomens',
        ],
        'contact' => [
            'vi' => 'lien-he',
            'en' => 'contact-us',
            'ja' => 'kontakuto',
            'ko' => 'mun-uihagi',
            'zh' => 'lianxiwomen',
        ],
    ],
];
