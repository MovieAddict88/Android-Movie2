<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Phone Rental System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
    <div class="card" style="width: 100%; max-width: 400px;">
        <h2 style="text-align: center;">Admin Login</h2>
        <?php if (isset($error)) echo "<p style='color: var(--danger);'>$error</p>"; ?>
        <form method="post">
            <div style="margin-bottom: 1rem;">
                <label>Username</label>
                <input type="text" name="username" class="btn" style="width: 100%; border: 1px solid var(--border-color); cursor: text;" required>
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label>Password</label>
                <input type="password" name="password" class="btn" style="width: 100%; border: 1px solid var(--border-color); cursor: text;" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <p style="margin-top: 1rem; text-align: center; color: var(--text-muted); font-size: 0.8rem;">Default: admin / admin123</p>
    </div>
</body>
</html>
