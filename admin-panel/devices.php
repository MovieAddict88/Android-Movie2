<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle actions
if (isset($_POST['add_device'])) {
    $device_id = $_POST['device_id'];
    $model = $_POST['model'];
    $owner = $_POST['owner_name'];
    $rental_end = $_POST['rental_end'];

    $stmt = $pdo->prepare("INSERT INTO devices (device_id, model, owner_name, rental_end_time) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE model=?, owner_name=?, rental_end_time=?");
    $stmt->execute([$device_id, $model, $owner, $rental_end, $model, $owner, $rental_end]);
}

if (isset($_GET['lock'])) {
    $id = $_GET['lock'];
    $stmt = $pdo->prepare("UPDATE devices SET is_locked = 1 WHERE id = ?");
    $stmt->execute([$id]);

    // Add command for app
    $device_id = $pdo->query("SELECT device_id FROM devices WHERE id = $id")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'lock')");
    $stmt->execute([$device_id]);
    header("Location: devices.php");
    exit;
}

if (isset($_GET['unlock'])) {
    $id = $_GET['unlock'];
    $stmt = $pdo->prepare("UPDATE devices SET is_locked = 0 WHERE id = ?");
    $stmt->execute([$id]);

    $device_id = $pdo->query("SELECT device_id FROM devices WHERE id = $id")->fetchColumn();
    $stmt = $pdo->prepare("INSERT INTO commands (device_id, command) VALUES (?, 'unlock')");
    $stmt->execute([$device_id]);
    header("Location: devices.php");
    exit;
}

$devices = $pdo->query("SELECT * FROM devices ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Devices - Phone Rental System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container header-content">
            <h1 style="font-size: 1.5rem; margin-bottom: 0;">Rental Admin</h1>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="devices.php">Devices</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <h2>Add / Update Device</h2>
            <form method="post" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <input type="text" name="device_id" placeholder="Device ID (IMEI/Serial)" class="btn" style="border: 1px solid var(--border-color); cursor: text;" required>
                <input type="text" name="model" placeholder="Model Name" class="btn" style="border: 1px solid var(--border-color); cursor: text;">
                <input type="text" name="owner_name" placeholder="Renter Name" class="btn" style="border: 1px solid var(--border-color); cursor: text;">
                <input type="datetime-local" name="rental_end" class="btn" style="border: 1px solid var(--border-color); cursor: text;">
                <button type="submit" name="add_device" class="btn btn-primary">Save Device</button>
            </form>
        </div>

        <div class="card">
            <h2>Device List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Model</th>
                        <th>Owner</th>
                        <th>Rental Ends</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($device['device_id']); ?></td>
                        <td><?php echo htmlspecialchars($device['model']); ?></td>
                        <td><?php echo htmlspecialchars($device['owner_name']); ?></td>
                        <td><?php echo $device['rental_end_time']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $device['is_locked'] ? 'status-inactive' : 'status-active'; ?>">
                                <?php echo $device['is_locked'] ? 'LOCKED' : 'ACTIVE'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($device['is_locked']): ?>
                                <a href="?unlock=<?php echo $device['id']; ?>" class="btn btn-primary">Unlock</a>
                            <?php else: ?>
                                <a href="?lock=<?php echo $device['id']; ?>" class="btn btn-danger">Lock</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
