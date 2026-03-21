<?php
// logger.php
$debug_logs = [];

function forcekes_log($message, $data = null) {
    global $debug_logs;
    $timestamp = date('H:i:s');
    $logEntry = "[$timestamp] $message";
    if ($data !== null) {
        $logEntry .= " | " . json_encode($data, JSON_PRETTY_PRINT);
    }
    $debug_logs[] = $logEntry;
}

function display_forcekes_logs() {
    global $debug_logs;
    echo "<div style='background:#000; color:#0f0; padding:20px; font-family:monospace; border:2px solid #333; margin-top:20px; white-space:pre-wrap; overflow-x:auto;'>";
    echo "<h3>🕵️ DEBUG TRACE:</h3>";
    foreach ($debug_logs as $log) {
        echo htmlspecialchars($log) . "\n\n";
    }
    echo "</div>";
}