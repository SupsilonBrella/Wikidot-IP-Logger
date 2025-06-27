<?php
error_reporting(0);
$user = $_GET['user'] ?? 'unknown';
$site = $_GET['site'] ?? 'unknown';

function getIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}

$ip = getIP();
$time = date('Y-m-d H:i:s');
file_put_contents('log.csv', "$user,$site,$ip,$time\n", FILE_APPEND | LOCK_EX);
?>
