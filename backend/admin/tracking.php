<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Mock active trips since we don't have a real DB update mechanism in the simulation yet
$activeTrips = [
    [
        'id' => 1,
        'user' => 'John Doe',
        'car' => 'Toyota Vios',
        'pickup' => [14.5995, 120.9842],
        'dropoff' => [14.6333, 121.0333],
        'current' => [14.6150, 121.0050],
        'status' => 'In Progress'
    ],
    [
        'id' => 2,
        'user' => 'Jane Smith',
        'car' => 'Mitsubishi Montero',
        'pickup' => [14.5800, 121.0000],
        'dropoff' => [14.5500, 121.0500],
        'current' => [14.5700, 121.0150],
        'status' => 'Heading to Pickup'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin GPS Tracking - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #admin-map { height: 500px; width: 100%; border-radius: 12px; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar could go here, but let's keep it simple -->
        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-6xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Live Fleet Tracking</h1>
                        <p class="text-gray-600">Monitor all active trips in real-time</p>
                    </div>
                    <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2">
                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                            <div id="admin-map"></div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Active Trips</h2>
                        <?php foreach($activeTrips as $trip): ?>
                        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 transition-colors cursor-pointer trip-card" 
                             data-lat="<?= $trip['current'][0] ?>" 
                             data-lng="<?= $trip['current'][1] ?>"
                             data-pickup-lat="<?= $trip['pickup'][0] ?>"
                             data-pickup-lng="<?= $trip['pickup'][1] ?>"
                             data-drop-lat="<?= $trip['dropoff'][0] ?>"
                             data-drop-lng="<?= $trip['dropoff'][1] ?>"
                             data-user="<?= $trip['user'] ?>">
                            <div class="flex justify-between items-start mb-3">
                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full uppercase"><?= $trip['status'] ?></span>
                                <span class="text-xs text-gray-400">#TRP-<?= $trip['id'] ?></span>
                            </div>
                            <h3 class="font-bold text-gray-800"><?= $trip['user'] ?></h3>
                            <p class="text-sm text-gray-500 mb-4"><?= $trip['car'] ?></p>
                            
                            <div class="space-y-2">
                                <div class="flex items-center text-xs">
                                    <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                                    <span class="text-gray-400 mr-2">From:</span>
                                    <span class="text-gray-600 truncate"><?= $trip['pickup'][0] ?>, <?= $trip['pickup'][1] ?></span>
                                </div>
                                <div class="flex items-center text-xs">
                                    <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                                    <span class="text-gray-400 mr-2">To:</span>
                                    <span class="text-gray-600 truncate"><?= $trip['dropoff'][0] ?>, <?= $trip['dropoff'][1] ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('admin-map').setView([14.5995, 120.9842], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var markers = [];
        var lines = [];

        function clearMap() {
            markers.forEach(m => map.removeLayer(m));
            lines.forEach(l => map.removeLayer(l));
            markers = [];
            lines = [];
        }

        document.querySelectorAll('.trip-card').forEach(card => {
            card.addEventListener('click', function() {
                clearMap();
                var lat = parseFloat(this.dataset.lat);
                var lng = parseFloat(this.dataset.lng);
                var pLat = parseFloat(this.dataset.pickupLat);
                var pLng = parseFloat(this.dataset.pickupLng);
                var dLat = parseFloat(this.dataset.dropLat);
                var dLng = parseFloat(this.dataset.dropLng);
                var user = this.dataset.user;

                // Pickup
                markers.push(L.marker([pLat, pLng], {icon: L.divIcon({className:'', html:'<div class="bg-green-500 w-3 h-3 rounded-full border-2 border-white"></div>'})}).addTo(map));
                // Dropoff
                markers.push(L.marker([dLat, dLng], {icon: L.divIcon({className:'', html:'<div class="bg-red-500 w-3 h-3 rounded-full border-2 border-white"></div>'})}).addTo(map));
                // Current
                var carMarker = L.marker([lat, lng], {
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="bg-blue-600 p-2 rounded-full shadow-lg text-white"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg></div>',
                        iconAnchor: [16, 16]
                    })
                }).addTo(map).bindPopup("<b>" + user + "</b> is here").openPopup();
                markers.push(carMarker);

                var line = L.polyline([[pLat, pLng], [dLat, dLng]], {color: 'blue', weight: 2, dashArray: '5, 5'}).addTo(map);
                lines.push(line);

                map.fitBounds(line.getBounds(), {padding: [50, 50]});
            });
        });

        // Initialize with first trip
        document.querySelector('.trip-card').click();
    </script>
</body>
</html>
