<?php
// Default classroom/dev DB config.
// Do not put real production secrets here.

$CARE_DB = [
    'host' => '1.1.2.10',
    'user' => 'user',
    'pass' => '1111',
    'name' => 'care',
    'port' => 3306,
];

$localConfig = __DIR__ . '/config.local.php';

if (file_exists($localConfig)) {
    $local = require $localConfig;

    if (is_array($local)) {
        $CARE_DB = array_merge($CARE_DB, $local);
    }
}

function care_db_connect()
{
    global $CARE_DB;

    $link = mysqli_connect(
        $CARE_DB['host'],
        $CARE_DB['user'],
        $CARE_DB['pass'],
        $CARE_DB['name'],
        (int)$CARE_DB['port']
    );

    if (!$link) {
        die('DB 연결 실패');
    }

    mysqli_set_charset($link, 'utf8mb4');

    return $link;
}