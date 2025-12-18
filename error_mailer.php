<?php
// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    die('Direct access not allowed');
}

function sendErrorEmail($errorCode, $errorDetails)
{
    $to = "tvrealtorhtx@yahoo.com";  // Replace with your email
    $subject = "Critical Error: " . $errorCode;

    $message = "An error occurred in the web application:\n\n";
    $message .= "Error Code: " . $errorCode . "\n";
    $message .= "Error Details: " . $errorDetails . "\n";
    $message .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $message .= "Server: " . gethostname() . "\n";

    $headers = [
        'From' => 'webform@spacecityagents.com',
        'X-Mailer' => 'PHP/' . phpversion(),
        'X-Priority' => '1', // High priority
    ];

    // Attempt to send email
    $mailSent = @mail($to, $subject, $message, implode("\r\n", $headers));

    // Log to file if mail fails
    if (!$mailSent) {
        error_log($message, 3, "/var/www/html/email_error_log.txt");
    }

    return $mailSent;
}
?>