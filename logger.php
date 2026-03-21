<?php
// logger.php
function forcekes_log($message, $data = null) {
    $logFile = __DIR__ . '/debug_oauth.log';
    $timestamp = date('Y-m-d H:i:s');
    $content = "[$timestamp] $message";
    
    if ($data !== null) {
        $content .= " | Data: " . json_encode($data);
    }
    
    file_put_contents($logFile, $content . PHP_EOL, FILE_APPEND);
}