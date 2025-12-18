<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Logging function to help debug
function debugLog($message)
{
    error_log($message . "\n", 3, "/var/www/html/debug_log.txt");
}

// Log the incoming request details
debugLog("Request Method: " . $_SERVER['REQUEST_METHOD']);
debugLog("Request URI: " . $_SERVER['REQUEST_URI']);
debugLog("Request Body: " . file_get_contents('php://input'));

// CORS and headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    debugLog("Handling OPTIONS request");
    http_response_code(200);
    exit();
}

// Ensure only POST method is processed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debugLog("Invalid method: " . $_SERVER['REQUEST_METHOD']);

    // Send email about method error
    $errorDetails = "Invalid HTTP method attempted: " . $_SERVER['REQUEST_METHOD'];

    // Attempt to send email directly if includes are not working
    $to = "your-email@example.com";
    $subject = "Critical Error: INVALID_METHOD";
    $message = "Error Details: " . $errorDetails;
    $headers = "From: webform@spacecityagents.com";

    mail($to, $subject, $message, $headers);

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit();
}

// Include secure error mailer and logger
define('SECURE_ACCESS', true);

// Attempt to include files
$includePaths = [
    '/var/www/html/error_mailer.php',
    '/opt/secure_scripts/error_mailer.php'
];

$includeSuccess = false;
foreach ($includePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $includeSuccess = true;
        debugLog("Successfully included: " . $path);
        break;
    }
}

if (!$includeSuccess) {
    debugLog("Could not include error_mailer.php");

    // Fallback email sending
    $to = "your-email@example.com";
    $subject = "Critical Error: INCLUDE_FAILURE";
    $message = "Could not include error_mailer.php";
    $headers = "From: webform@spacecityagents.com";

    mail($to, $subject, $message, $headers);

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server configuration error']);
    exit();
}

// Get input data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

debugLog("Decoded input: " . print_r($data, true));

// Validate input
if (!$data || !isset($data['errorCode']) || !isset($data['errorDetails'])) {
    debugLog("Invalid input received");

    // Send email about validation error
    $errorDetails = "Invalid input received: " . print_r($data, true);
    sendErrorEmail('INPUT_VALIDATION_ERROR', $errorDetails);

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit();
}

// Attempt to log and send email
try {
    // Log the error (if function exists)
    if (function_exists('logErrorToFile')) {
        logErrorToFile($data['errorCode'], $data['errorDetails']);
    }

    // Send email notification
    $emailResult = sendErrorEmail($data['errorCode'], $data['errorDetails']);

    debugLog("Email sending result: " . ($emailResult ? 'Success' : 'Failure'));

    // Respond with status
    echo json_encode([
        'status' => 'success',
        'email_sent' => $emailResult
    ]);
} catch (Exception $e) {
    debugLog("Exception: " . $e->getMessage());

    // Fallback email
    $to = "your-email@example.com";
    $subject = "Critical Error: EXCEPTION";
    $message = "Exception: " . $e->getMessage();
    $headers = "From: webform@spacecityagents.com";

    mail($to, $subject, $message, $headers);

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
exit();
?>