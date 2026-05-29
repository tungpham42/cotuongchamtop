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
        'human.play' => [
            'vi' => 'choi-mot-minh',
            'en' => 'play-alone',
            'ja' => 'ichi-nin-de-asobu',
            'ko' => 'honja-nolda',
            'zh' => 'duchu',
        ],
        'puzzle.setup' => [
            'vi' => 'co-the',
            'en' => 'puzzle',
            'ja' => 'pazuru',
            'ko' => 'peojeul',
            'zh' => 'mi',
        ],
        'puzzle.board' => [
            'vi' => 'co-the/{board}',
            'en' => 'puzzle/{board}',
            'ja' => 'pazuru/{board}',
            'ko' => 'peojeul/{board}',
            'zh' => 'mi/{board}',
        ],
        'board.self' => [
            'vi' => 'ban-co/{fen}',
            'en' => 'board/{fen}',
            'ja' => 'bodo/{fen}',
            'ko' => 'bodeu/{fen}',
            'zh' => 'ban/{fen}',
        ],
        'board.ai.easiest' => [
            'vi' => 'ban-co-de-nhat/{fen}',
            'en' => 'easiest-board/{fen}',
            'ja' => 'mottomo-kantanna-bodo/{fen}',
            'ko' => 'gajang-swiun-bodeu/{fen}',
            'zh' => 'zuijiandandeban/{fen}',
        ],
        'board.ai.newbie' => [
            'vi' => 'ban-co-moi-choi/{fen}',
            'en' => 'newbie-board/{fen}',
            'ja' => 'shoshinsha-bodo/{fen}',
            'ko' => 'nyubi-bodeu/{fen}',
            'zh' => 'xinshouban/{fen}',
        ],
        'board.ai.easy' => [
            'vi' => 'ban-co-de/{fen}',
            'en' => 'easy-board/{fen}',
            'ja' => 'kantan-bodo/{fen}',
            'ko' => 'iji-bodeu/{fen}',
            'zh' => 'jianyiban/{fen}',
        ],
        'board.ai.normal' => [
            'vi' => 'ban-co-binh-thuong/{fen}',
            'en' => 'normal-board/{fen}',
            'ja' => 'tsujo-bodo/{fen}',
            'ko' => 'nomol-bodeu/{fen}',
            'zh' => 'putongban/{fen}',
        ],
        'board.ai.hard' => [
            'vi' => 'ban-co-kho/{fen}',
            'en' => 'hard-board/{fen}',
            'ja' => 'hado-bodo/{fen}',
            'ko' => 'hadeu-bodeu/{fen}',
            'zh' => 'yingban/{fen}',
        ],
        'board.ai.hardest' => [
            'vi' => 'ban-co-kho-nhat/{fen}',
            'en' => 'hardest-board/{fen}',
            'ja' => 'mottomo-muzukashi-bodo/{fen}',
            'ko' => 'gajang-dandanhan-bodeu/{fen}',
            'zh' => 'zuiyingban/{fen}',
        ],
        'puzzle.ai.solve' => [
            'vi' => 'giai-co-the/{fen}',
            'en' => 'solve-puzzle/{fen}',
            'ja' => 'pazuru-o-toku/{fen}',
            'ko' => 'pojeureul-pulda/{fen}',
            'zh' => 'jiejuenanti/{fen}',
        ],
        'ai.home' => [
            'vi' => '/',
            'en' => 'en',
            'ja' => 'ja',
            'ko' => 'ko',
            'zh' => 'zh',
        ],
        'ai.easiest' => [
            'vi' => 'de-nhat', 'en' => 'easiest', 'ja' => 'mottomo-kantan', 'ko' => 'gajang-swiun', 'zh' => 'zuijiandan'
        ],
        'ai.newbie' => [
            'vi' => 'moi-choi', 'en' => 'newbie', 'ja' => 'shoshinsha', 'ko' => 'nyubi', 'zh' => 'xinshou'
        ],
        'ai.easy' => [
            'vi' => 'de', 'en' => 'easy', 'ja' => 'kantan', 'ko' => 'iji', 'zh' => 'jianyiban'
        ],
        'ai.normal' => [
            'vi' => 'binh-thuong', 'en' => 'normal', 'ja' => 'tsujo', 'ko' => 'nomol', 'zh' => 'putong'
        ],
        'ai.hard' => [
            'vi' => 'kho', 'en' => 'hard', 'ja' => 'hado', 'ko' => 'hadeu', 'zh' => 'ying'
        ],
        'ai.hardest' => [
            'vi' => 'kho-nhat', 'en' => 'hardest', 'ja' => 'mottomo-muzukashi', 'ko' => 'gajang-dandanhan', 'zh' => 'zuiying'
        ],
        'ai.master' => [
            'vi' => 'kien-tuong', 'en' => 'master', 'ja' => 'masuta', 'ko' => 'maseuteo', 'zh' => 'dashi'
        ],
    ],
];
