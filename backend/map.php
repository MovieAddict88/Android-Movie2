<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$stmt = $pdo->query("SELECT * FROM cars WHERE availability_status = 1");
$cars = $stmt->fetchAll();

// Add mock coordinates if not present
foreach ($cars as $key => $car) {
    if (!isset($car['latitude']) || !$car['latitude']) {
        $cars[$key]['latitude'] = 14.5995 + (mt_rand(-100, 100) / 1000.0);
        $cars[$key]['longitude'] = 120.9842 + (mt_rand(-100, 100) / 1000.0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Car Map - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: calc(100vh - 80px); width: 100%; }
        .map-container { position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin: 20px; }
        .floating-tile {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 450px;
            display: none;
            align-items: center;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .floating-tile.active {
            display: flex;
            animation: slideUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes slideUp {
            from { transform: translate(-50%, 120%); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }
        .custom-marker {
            background-color: #2563eb;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="map-container">
        <div id="map"></div>
        
        <!-- Floating Interactive Tile View -->
        <div id="floating-tile" class="floating-tile">
            <!-- Content dynamically injected -->
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([14.5995, 120.9842], 13);

        L.control.zoom({ position: 'topright' }).addTo(map);
        L.control.attribution({ position: 'bottomright' }).addTo(map);

        var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors',
            detectRetina: true
        }).addTo(map);

        var cars = <?php echo json_encode($cars); ?>;
        var markers = [];

        cars.forEach(function(car) {
            var marker = L.marker([car.latitude, car.longitude]).addTo(map);
            
            marker.on('click', function() {
                showFloatingTile(car);
                map.panTo([car.latitude, car.longitude]);
            });
            
            markers.push(marker);
        });

        function showFloatingTile(car) {
            var tile = document.getElementById('floating-tile');
            var imageUrl = car.image || 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=300&q=80';
            
            tile.innerHTML = `
                <img src="${imageUrl}" class="w-28 h-28 object-cover rounded-xl shadow-md mr-5">
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <h3 class="font-bold text-xl text-gray-800">${car.brand} ${car.model}</h3>
                        <button onclick="hideFloatingTile()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    <p class="text-blue-600 font-bold text-lg mt-1">$${car.daily_rate}<span class="text-sm text-gray-500 font-normal">/day</span></p>
                    <div class="mt-4 flex gap-3">
                        <a href="booking.php?id=${car.id}" class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">Book Now</a>
                    </div>
                </div>
            `;
            tile.classList.add('active');
        }

        function hideFloatingTile() {
            document.getElementById('floating-tile').classList.remove('active');
        }

        // Close tile when clicking on map
        map.on('click', function() {
            hideFloatingTile();
        });
    </script>
</body>
</html>
