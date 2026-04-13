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
    <title>GPS Interactive Map - CarRental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: calc(100vh - 80px); width: 100%; z-index: 1; }
        .map-container { position: relative; height: calc(100vh - 80px); overflow: hidden; }
        
        .ui-panel {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 320px;
        }

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
        
        .step-indicator {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            margin-right: 10px;
        }
        .step-dot.active {
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 10px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'includes/navbar.php'; ?>

    <div class="map-container">
        <div id="map"></div>
        
        <!-- Interactive UI Panel -->
        <div class="ui-panel">
            <h2 class="text-xl font-bold mb-4 text-gray-800">GPS Tracker</h2>
            
            <div id="setup-view">
                <p class="text-sm text-gray-600 mb-4">Select your points on the map to start tracking.</p>
                
                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-bold">Pickup Point</p>
                            <p id="pickup-status" class="text-sm text-gray-800">Click on map...</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center mr-3">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 uppercase font-bold">Drop Point</p>
                            <p id="dropoff-status" class="text-sm text-gray-800">Waiting...</p>
                        </div>
                    </div>
                </div>

                <button id="start-btn" disabled class="w-full mt-6 bg-blue-600 text-white py-3 rounded-xl font-bold opacity-50 cursor-not-allowed transition-all hover:bg-blue-700">
                    Start Simulation
                </button>
            </div>

            <div id="tracking-view" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full animate-pulse">LIVE TRACKING</span>
                    <button id="reset-btn" class="text-sm text-blue-600 hover:underline">Reset</button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Estimated Time</span>
                        <span id="eta" class="font-bold text-gray-800">12 mins</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Distance</span>
                        <span id="distance" class="font-bold text-gray-800">4.2 km</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="progress-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs text-blue-600 font-bold uppercase mb-1">Status</p>
                    <p id="current-status" class="text-sm text-blue-800">Driver is heading to pickup...</p>
                </div>
            </div>
        </div>
        
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

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var pickupMarker = null;
        var dropoffMarker = null;
        var driverMarker = null;
        var routeLine = null;
        
        var points = [];

        map.on('click', function(e) {
            if (points.length === 0) {
                // Set Pickup
                points.push(e.latlng);
                pickupMarker = L.marker(e.latlng, {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: "<div style='background-color:#22c55e; width:16px; height:16px; border-radius:50%; border:3px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.3);'></div>",
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(map);
                document.getElementById('pickup-status').innerText = e.latlng.lat.toFixed(4) + ", " + e.latlng.lng.toFixed(4);
                document.getElementById('dropoff-status').innerText = "Click on map...";
            } else if (points.length === 1) {
                // Set Dropoff
                points.push(e.latlng);
                dropoffMarker = L.marker(e.latlng, {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: "<div style='background-color:#ef4444; width:16px; height:16px; border-radius:50%; border:3px solid white; box-shadow:0 2px 4px rgba(0,0,0,0.3);'></div>",
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    })
                }).addTo(map);
                document.getElementById('dropoff-status').innerText = e.latlng.lat.toFixed(4) + ", " + e.latlng.lng.toFixed(4);
                
                // Enable Start Button
                const btn = document.getElementById('start-btn');
                btn.disabled = false;
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                
                // Draw line
                routeLine = L.polyline(points, {color: '#2563eb', weight: 4, dashArray: '10, 10'}).addTo(map);
                map.fitBounds(routeLine.getBounds(), {padding: [50, 50]});
            }
        });

        document.getElementById('start-btn').addEventListener('click', function() {
            document.getElementById('setup-view').classList.add('hidden');
            document.getElementById('tracking-view').classList.remove('hidden');
            startSimulation();
        });

        document.getElementById('reset-btn').addEventListener('click', function() {
            location.reload();
        });

        function startSimulation() {
            var start = points[0];
            var end = points[1];
            
            driverMarker = L.marker(start, {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div style='background-color:#2563eb; width:24px; height:24px; border-radius:50%; border:3px solid white; box-shadow:0 4px 6px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center; color:white;'><svg viewBox='0 0 24 24' width='14' height='14' fill='currentColor'><path d='M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.27-3.82c.07-.21.27-.35.49-.35h10.48c.22 0 .42.14.49.35L19 11H5z'/></svg></div>",
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                })
            }).addTo(map);

            var steps = 100;
            var currentStep = 0;
            
            var interval = setInterval(function() {
                currentStep++;
                var lat = start.lat + (end.lat - start.lat) * (currentStep / steps);
                var lng = start.lng + (end.lng - start.lng) * (currentStep / steps);
                
                driverMarker.setLatLng([lat, lng]);
                
                var progress = (currentStep / steps) * 100;
                document.getElementById('progress-bar').style.width = progress + "%";
                
                if (progress < 50) {
                    document.getElementById('current-status').innerText = "Driver is on the way to destination...";
                } else if (progress < 90) {
                    document.getElementById('current-status').innerText = "Driver is almost there...";
                } else {
                    document.getElementById('current-status').innerText = "Arrived at destination!";
                }
                
                if (currentStep % 10 === 0) {
                    // Send update to admin every 10 steps
                    fetch('api/update_location.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            lat: lat,
                            lng: lng,
                            user_id: 1, // Mock user
                            car_id: 1   // Mock car
                        })
                    });
                }
                
                if (currentStep >= steps) {
                    clearInterval(interval);
                }
            }, 100);
        }
    </script>
</body>
</html>
