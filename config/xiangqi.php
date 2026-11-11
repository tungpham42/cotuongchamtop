<?php

return [
    'initial_fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
    // Where the worker unix sockets live. Must be writable by the user
    // that runs the xiangqi:engine-worker processes AND readable/writable
    // by the user OpenLiteSpeed runs lsphp as (usually the same user, or
    // put them in the same group — see deploy/README.md).
    'socket_dir' => env('XIANGQI_SOCKET_DIR', storage_path('app/xiangqi')),

    // Must match the numprocs value in the supervisor config (set to 2 for VPS memory stability).
    'worker_count' => env('XIANGQI_WORKER_COUNT', 2),

    'php_binary' => env('XIANGQI_PHP_BINARY'),

    // A game with no result that has not moved for this many hours is treated
    // as abandoned and is no longer listed as "live" (see Room::scopeLive).
    // Set to 0 to disable the cutoff.
    'live_window_hours' => (int) env('XIANGQI_LIVE_WINDOW_HOURS', 24),
];
