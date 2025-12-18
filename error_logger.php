<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed');
}

function logErrorToFile($errorCode, $errorDetails)
{
    $logEntry = date('Y-m-d H:i:s') . " | ";
    $logEntry .= "Code: " . $errorCode . " | ";
    $logEntry .= "Details: " . $errorDetails . " | ";
    $logEntry .= "Server: " . gethostname() . "\n";

    // Log to main error log
    error_log($logEntry, 3, "/var/www/html/error_log.txt");

    // Optional: Create a separate log for this specific error code
    $specificLogPath = "/var/www/html/" . $errorCode . "error_log.txt";
    error_log($logEntry, 3, $specificLogPath);
}
?>