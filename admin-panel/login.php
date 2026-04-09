<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "CSRF validation failed";
    } else {
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
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
    <div class="card" style="width: 100%; max-width: 420px; padding: 3rem; border: none; box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 64px; height: 64px; background: var(--primary-color); border-radius: 1rem; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; color: white; font-size: 2rem; font-weight: bold;">
                PR
            </div>
            <h1 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 0.5rem;">Welcome Back</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Sign in to manage rentals</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo get_csrf_token(); ?>">

            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Username</label>
                <input type="text" name="username" placeholder="Enter username" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 0.75rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'" required>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem;">Password</label>
                <input type="password" name="password" placeholder="••••••••" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 0.75rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--primary-color)'" onblur="this.style.borderColor='var(--border-color)'" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary" style="width: 100%; height: 3.25rem; font-size: 1rem;">
                Login to Dashboard
            </button>
        </form>

        <div style="margin-top: 2rem; text-align: center;">
            <p style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Demo Credentials</p>
            <p style="color: #475569; font-size: 0.875rem; margin-top: 0.25rem;">admin / admin123</p>
        </div>
    </div>
</body>
</html>
