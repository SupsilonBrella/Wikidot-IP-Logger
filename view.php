<?php
session_start();
$password = 'password';

if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['pass'] === $password) {
        $_SESSION['authenticated'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    ?>
    <form method="post">
        <h2>Enter the password</h2>
        <input type="password" name="pass" required />
        <input type="submit" value="Login" />
        <?php if (isset($_POST['pass'])) echo "<p style='color:red;'>The password you entered is incorrect.</p>"; ?>
    </form>
    <?php exit();
}

$log_file = 'log.csv';
$keyword = trim($_GET['search'] ?? '');
$time_range = $_GET['range'] ?? '';
$start_date = isset($_GET['start']) ? strtotime($_GET['start'] . ' 00:00:00') : 0;
$end_date = isset($_GET['end']) ? strtotime($_GET['end'] . ' 23:59:59') : 0;
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 100;
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

$records = [];
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
                $records[] = [$user, $site, $ip, $time_str];
            }
        }
    }
$records = array_reverse($records);
}

$total = count($records);
$total_pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;
$records_paginated = array_slice($records, $offset, $per_page);
?>
<h2>Records</h2>

<form action="logout.php" method="post" style="display:inline;">
    <button type="submit">Logout</button>
</form>
<form action="export.php" method="get" style="display:inline; margin-left:10px;">
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($keyword); ?>">
    <input type="hidden" name="start" value="<?php echo htmlspecialchars($_GET['start'] ?? ''); ?>">
    <input type="hidden" name="end" value="<?php echo htmlspecialchars($_GET['end'] ?? ''); ?>">
    <input type="hidden" name="range" value="<?php echo htmlspecialchars($time_range); ?>">
    <button type="submit">Export current IP log records</button>
</form>

<form method="get" style="margin-top: 20px;">
    <input type="text" name="search" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="USER / SITE / IP" />
    <label style="margin-left:10px;">Time range</label>
    <select name="range">
        <option value="" <?php if ($time_range === '') echo 'selected'; ?>>All</option>
        <option value="last24h" <?php if ($time_range === 'last24h') echo 'selected'; ?>>Last 24h</option>
        <option value="last7d" <?php if ($time_range === 'last7d') echo 'selected'; ?>>Last 7days</option>
        <option value="thismonth" <?php if ($time_range === 'thismonth') echo 'selected'; ?>>This month</option>
        <option value="thisyear" <?php if ($time_range === 'thisyear') echo 'selected'; ?>>This year</option>
    </select>
    <label style="margin-left:10px;">Custom time range</label>
    <input type="date" name="start" value="<?php echo htmlspecialchars($_GET['start'] ?? ''); ?>">
    —
    <input type="date" name="end" value="<?php echo htmlspecialchars($_GET['end'] ?? ''); ?>">
    <input type="submit" value="Search" style="margin-left:10px;" />
</form>

<table border="1" cellpadding="6" cellspacing="0" style="margin-top:10px;">
    <tr><th>USER</th><th>SITE</th><th>IP</th><th>TIME</th></tr>
    <?php if (empty($records_paginated)): ?>
        <tr><td colspan="4">No matching results</td></tr>
    <?php else: ?>
        <?php foreach ($records_paginated as $row): ?>
            <tr>
                <?php foreach ($row as $col): ?>
                    <td><?php echo htmlspecialchars($col); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<div style="margin-top: 15px;">
<?php
for ($i = 1; $i <= $total_pages; $i++) {
    $link = "?page=$i";
    if ($keyword !== '') $link .= "&search=" . urlencode($keyword);
    if ($time_range !== '') $link .= "&range=" . urlencode($time_range);
    if (!empty($_GET['start'])) $link .= "&start=" . urlencode($_GET['start']);
    if (!empty($_GET['end'])) $link .= "&end=" . urlencode($_GET['end']);
    echo ($i === $page) ? "<strong>$i</strong> " : "<a href='$link'>$i</a> ";
}
?>
</div>
