<?php
// includes/db.php
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'phone_rental');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Security Configuration
define('API_KEY', 'your_secure_api_key_here_123'); // In production, this should be in a separate config or environment variable

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently fail if not installed yet, but log if needed
}

/**
 * Validates the API Key from the X-API-Key header
 */
function validate_api_key() {
    $headers = getallheaders();
    $provided_key = $headers['X-API-Key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? null;

    if ($provided_key !== API_KEY) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid or missing API Key']);
        exit;
    }
}
?>
