<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);

    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $message])) {
        $success = "Message sent! We'll get back to you soon.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-2xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Contact Us</h1>
        
        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6"><?= $success ?></div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-xl shadow-lg">
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-600" required>
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Message</label>
                    <textarea name="message" rows="5" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-600" required></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">Send Message</button>
            </form>
        </div>
    </div>
</body>
</html>
