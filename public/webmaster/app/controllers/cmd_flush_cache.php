<?php
$user_key = _v($_GET, 'key', '');
$app_key = ConfigFactory::load("app")->app_command_key;
if(empty($app_key) OR $user_key !== $app_key) {
    exit('Access Denied');
}

$tables = array(
    'alexa', 'backlinks', 'diagnostic',
    'location', 'search', 'social',
    'whois'
);

foreach ($tables as $table) {
    $sql = "TRUNCATE ". DB::ins()->table($table);
    DB::ins()->execute($sql);
}
exit('Cache has been flushed');