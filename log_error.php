<?php
// Allow POST requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure only POST method is processed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Log this error
    error_log("Invalid method: " . $_SERVER['REQUEST_METHOD'], 3, "/var/www/html/method_error_log.txt");

    // Send email about method error
    $errorDetails = "Invalid HTTP method attempted: " . $_SERVER['REQUEST_METHOD'];
    sendErrorEmail('INVALID_METHOD', $errorDetails);

    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method Not Allowed']));
}

// Include secure error mailer and logger
define('SECURE_ACCESS', true);
require_once 'error_mailer.php';
require_once 'error_logger.php';

// Get input data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!$data || !isset($data['errorCode']) || !isset($data['errorDetails'])) {
    // Log validation error
    error_log("Invalid input: " . print_r($data, true), 3, "/var/www/html/input_error_log.txt");

    // Send email about validation error
    $errorDetails = "Invalid input received: " . print_r($data, true);
    sendErrorEmail('INPUT_VALIDATION_ERROR', $errorDetails);

    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Invalid input']));
}

// Log the error
logErrorToFile($data['errorCode'], $data['errorDetails']);

// Send email notification
$emailResult = sendErrorEmail($data['errorCode'], $data['errorDetails']);

// Respond with status
echo json_encode([
    'status' => 'success',
    'email_sent' => $emailResult
]);
exit;
?>