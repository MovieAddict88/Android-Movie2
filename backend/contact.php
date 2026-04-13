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
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 400px; width: 100%; border-radius: 12px; margin-top: 20px; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-8 text-center">Contact Us</h1>
        
        <?php if($success): ?>
            <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-6"><?= $success ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
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

            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-xl font-bold mb-4">Our Location</h2>
                <div id="map"></div>
                <div class="mt-4 space-y-2">
                    <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-2 text-blue-600"></i> 123 Car Rental Blvd, Luxury City</p>
                    <p class="text-gray-600"><i class="fas fa-phone mr-2 text-blue-600"></i> +1 234 567 890</p>
                    <p class="text-gray-600"><i class="fas fa-envelope mr-2 text-blue-600"></i> info@carrental.com</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        var map = L.map('map').setView([14.5995, 120.9842], 13); // Manila coordinates as example

        // OSM Layer
        var osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        });

        // Satellite Layer (Esri World Imagery)
        var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EBP, and the GIS User Community'
        });

        // Add default layer
        osm.addTo(map);

        // Add layer control
        var baseMaps = {
            "Standard Map": osm,
            "Satellite View": satellite
        };
        L.control.layers(baseMaps).addTo(map);

        // Add a marker
        L.marker([14.5995, 120.9842]).addTo(map)
            .bindPopup('<b>CarRental Main Office</b><br>Visit us here!')
            .openPopup();
    </script>
</body>
</html>
