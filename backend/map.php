<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$stmt = $pdo->query("SELECT * FROM cars WHERE availability_status = 1");
$cars = $stmt->fetchAll();

// Add mock coordinates since they are not in the DB yet
foreach ($cars as $key => $car) {
    // Generate some random coordinates around Manila for demo purposes
    $cars[$key]['latitude'] = 14.5995 + (mt_rand(-100, 100) / 1000.0);
    $cars[$key]['longitude'] = 120.9842 + (mt_rand(-100, 100) / 1000.0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Map - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: calc(100 vh - 64px); width: 100%; }
        .map-container { height: 600px; width: 100%; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Car Locations</h1>
            <a href="cars.php" class="text-blue-600 hover:underline">View List Mode</a>
        </div>

        <div class="map-container">
            <div id="map" style="height: 100%;"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([14.5995, 120.9842], 12);

        var osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        });

        var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        });

        osm.addTo(map);

        var baseMaps = {
            "Standard Map": osm,
            "Satellite View": satellite
        };
        L.control.layers(baseMaps).addTo(map);

        var cars = <?php echo json_encode($cars); ?>;

        cars.forEach(function(car) {
            var marker = L.marker([car.latitude, car.longitude]).addTo(map);
            var popupContent = `
                <div class="p-2">
                    <img src="${car.image || 'https://via.placeholder.com/150x100?text=Car'}" class="w-32 h-20 object-cover rounded mb-2">
                    <h3 class="font-bold">${car.brand} ${car.model}</h3>
                    <p class="text-sm text-blue-600 font-bold">$${car.daily_rate}/day</p>
                    <a href="booking.php?id=${car.id}" class="mt-2 block text-center bg-blue-600 text-white text-xs py-1 rounded hover:bg-blue-700">Book Now</a>
                </div>
            `;
            marker.bindPopup(popupContent);
        });
    </script>
</body>
</html>
