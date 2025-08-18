<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Utility function to send JSON responses
function sendResponse($status, $message, $data = null, $httpStatusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpStatusCode); // Ensure this is set before any output
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit();
}



function getBaseUrl() {
    // Construct the base URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . '://' . $host;
}

?>