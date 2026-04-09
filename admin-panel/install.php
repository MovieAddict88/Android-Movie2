<?php
// install.php
$config_file = 'includes/db.php';

if (isset($_POST['install'])) {
    $host = $_POST['host'];
    $dbname = $_POST['dbname'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname` ");

        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS devices (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(100) NOT NULL UNIQUE,
            model VARCHAR(100),
            owner_name VARCHAR(100),
            rental_end_time DATETIME,
            is_locked BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive') DEFAULT 'active',
            ip_address VARCHAR(45),
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS app_control (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(100) NOT NULL,
            package_name VARCHAR(255) NOT NULL,
            is_visible BOOLEAN DEFAULT TRUE,
            UNIQUE KEY device_package (device_id, package_name)
        );

        CREATE TABLE IF NOT EXISTS commands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            device_id VARCHAR(100) NOT NULL,
            command VARCHAR(50) NOT NULL,
            payload TEXT,
            status ENUM('pending', 'executed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        ";

        $pdo->exec($sql);

        // Default admin user
        $hashed_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES ('admin', ?)");
        $stmt->execute([$hashed_pass]);

        $config_content = "<?php
define('DB_HOST', '$host');
define('DB_NAME', '$dbname');
define('DB_USER', '$user');
define('DB_PASS', '$pass');

// Security Configuration
define('API_KEY', '" . bin2hex(random_bytes(16)) . "');

try {
    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException \$e) {
    // Connection failed
}

/**
 * Validates the API Key from the X-API-Key header
 */
function validate_api_key() {
    \$headers = getallheaders();
    \$provided_key = \$headers['X-API-Key'] ?? \$_SERVER['HTTP_X_API_KEY'] ?? null;

    if (\$provided_key !== API_KEY) {
        header('HTTP/1.1 401 Unauthorized');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Invalid or missing API Key']);
        exit;
    }
}
?>";
        file_put_contents($config_file, $config_content);

        echo "Installation successful! <a href='login.php'>Go to Login</a>";
        exit;
    } catch (PDOException $e) {
        $error = "Installation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Rental System - Installation</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f0f2f5; margin: 0; }
        .install-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #1a73e8; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input { width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #1a73e8; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #1557b0; }
        .error { color: red; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="install-box">
        <h2>Database Setup</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <div class="form-group">
                <label>MySQL Host</label>
                <input type="text" name="host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="dbname" value="phone_rental" required>
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="user" value="root" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="pass">
            </div>
            <button type="submit" name="install">Install System</button>
        </form>
    </div>
</body>
</html>
