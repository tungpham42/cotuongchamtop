<?php

return [
    'initial_fen' => 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR r - - 0 1',
    // Where the worker unix sockets live. Must be writable by the user
    // that runs the xiangqi:engine-worker processes AND readable/writable
    // by the user OpenLiteSpeed runs lsphp as (usually the same user, or
    // put them in the same group — see deploy/README.md).
    'socket_dir' => env('XIANGQI_SOCKET_DIR', storage_path('app/xiangqi')),

    // Must match the numprocs value in the supervisor config.
    'worker_count' => env('XIANGQI_WORKER_COUNT', 5),
];
