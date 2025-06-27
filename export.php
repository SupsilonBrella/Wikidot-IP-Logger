<?php
session_start();
if (!isset($_SESSION['authenticated'])) {
    header("Location: view.php");
    exit();
}

$log_file = 'log.csv';
$keyword = trim($_GET['search'] ?? '');
$time_range = $_GET['range'] ?? '';
$start_date = isset($_GET['start']) ? strtotime($_GET['start'] . ' 00:00:00') : 0;
$end_date = isset($_GET['end']) ? strtotime($_GET['end'] . ' 23:59:59') : 0;
$now = time();

if ($time_range === 'last24h') {
    $start_date = $now - 86400;
    $end_date = $now;
} elseif ($time_range === 'last7d') {
    $start_date = $now - 86400 * 7;
    $end_date = $now;
} elseif ($time_range === 'thismonth') {
    $start_date = strtotime(date('Y-m-01 00:00:00'));
    $end_date = $now;
} elseif ($time_range === 'thisyear') {
    $start_date = strtotime(date('Y-01-01 00:00:00'));
    $end_date = $now;
}

$filtered = [];
if (file_exists($log_file)) {
    foreach (file($log_file) as $line) {
        $cols = explode(',', trim($line));
        if (count($cols) === 4) {
            list($user, $site, $ip, $time_str) = $cols;
            $timestamp = strtotime($time_str);
            if ($timestamp === false) continue;
            if (
                ($keyword === '' || stripos($user, $keyword) !== false || stripos($site, $keyword) !== false || stripos($ip, $keyword) !== false) &&
                ($start_date === 0 || $timestamp >= $start_date) &&
                ($end_date === 0 || $timestamp <= $end_date)
            ) {
                $filtered[] = $cols;
                $filtered = array_reverse($filtered);
            }
        }
    }
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="filtered_log.csv"');
$output = fopen("php://output", "w");
foreach ($filtered as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit();
