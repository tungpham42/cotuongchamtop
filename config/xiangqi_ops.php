<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Secret ops URL key
    |--------------------------------------------------------------------------
    |
    | This value is part of the URL of the engine pool control page:
    |
    |     /_ops/{secret}/xiangqi-pool
    |
    | Set XIANGQI_OPS_SECRET in .env to a long random string (24+ characters).
    | If it is empty or too short, the page answers 404 for everyone.
    |
    | Generate one with:
    |     php -r "echo bin2hex(random_bytes(20)), PHP_EOL;"
    |
    */

    'secret' => env('XIANGQI_OPS_SECRET'),

];
