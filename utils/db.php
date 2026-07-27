<?php

// ===== CORS Headers (allow cross-origin requests from any domain) =====
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Default Content-Type for all API responses
header("Content-Type: application/json; charset=utf-8");

// Handle CORS preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$dbname = "quiz";
$username = "root";
$password = "";

// ===== Retry Logic for 'Too many connections' error =====
// On shared hosting, MySQL max_connections is often low (10-25).
// We retry up to 3 times with a brief delay between attempts.
$maxRetries = 3;
$retryDelay = 500000; // 0.5 seconds in microseconds
$lastException = null;

for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8;connect_timeout=5",
            $username,
            $password
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Reset exception on success
        $lastException = null;
        break;

    } catch(PDOException $e) {
        $lastException = $e;
        
        // Only retry on "Too many connections" error (SQLSTATE code 08004/1040)
        $errorMsg = $e->getMessage();
        if (
            strpos($errorMsg, '1040') !== false ||
            strpos($errorMsg, 'Too many connections') !== false ||
            strpos($errorMsg, '08004') !== false
        ) {
            if ($attempt < $maxRetries) {
                usleep($retryDelay * $attempt); // Increasing delay
                continue;
            }
        }
        
        // For other errors, don't retry
        break;
    }
}

// If all retries failed, handle gracefully based on caller context.
if ($lastException !== null) {
    http_response_code(503);
    define('DB_CONNECTION_FAILED', true);
    
    // API files get a clean JSON error (they don't wrap require_once in try/catch)
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    if (strpos($scriptPath, '/Api/') !== false) {
        header('Content-Type: application/json');
        die(json_encode([
            "status" => false,
            "message" => "Service temporarily unavailable. Please try again."
        ]));
    }
    
    // Admin/App pages get a throw so their try/catch can handle it (e.g., login.php)
    throw $lastException;
}
?>