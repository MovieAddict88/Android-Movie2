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

$page_title = 'Live Fleet Tracking';
$current_page = 'tracking';

include 'includes/header.php';
?>
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #admin-map { height: 600px; width: 100%; border-radius: 1.5rem; }
    .leaflet-container { font-family: 'Inter', sans-serif; }
</style>

<?php include 'includes/sidebar.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-100 sticky top-8">
            <div id="admin-map" class="z-0"></div>
        </div>
    </div>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-bold text-gray-800">Active Trips</h2>
            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase"><?= count($activeTrips) ?> Live</span>
        </div>
        
        <?php foreach($activeTrips as $trip): ?>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all cursor-pointer trip-card group" 
             data-lat="<?= $trip['current'][0] ?>" 
             data-lng="<?= $trip['current'][1] ?>"
             data-pickup-lat="<?= $trip['pickup'][0] ?>"
             data-pickup-lng="<?= $trip['pickup'][1] ?>"
             data-drop-lat="<?= $trip['dropoff'][0] ?>"
             data-drop-lng="<?= $trip['dropoff'][1] ?>"
             data-user="<?= $trip['user'] ?>">
            <div class="flex justify-between items-start mb-3">
                <?php
                $status_color = $trip['status'] === 'In Progress' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                ?>
                <span class="<?= $status_color ?> text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider"><?= $trip['status'] ?></span>
                <span class="text-[10px] font-mono text-gray-400">#TRP-<?= $trip['id'] ?></span>
            </div>
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 mr-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800"><?= $trip['user'] ?></h3>
                    <p class="text-xs text-gray-500"><?= $trip['car'] ?></p>
                </div>
            </div>
            
            <div class="space-y-3 relative before:absolute before:left-[7px] before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-100">
                <div class="flex items-center text-xs relative z-10">
                    <div class="w-3.5 h-3.5 rounded-full bg-white border-2 border-emerald-500 mr-3"></div>
                    <span class="text-gray-400 w-12 flex-shrink-0">From:</span>
                    <span class="text-gray-700 font-medium truncate"><?= $trip['pickup'][0] ?>, <?= $trip['pickup'][1] ?></span>
                </div>
                <div class="flex items-center text-xs relative z-10">
                    <div class="w-3.5 h-3.5 rounded-full bg-white border-2 border-rose-500 mr-3"></div>
                    <span class="text-gray-400 w-12 flex-shrink-0">To:</span>
                    <span class="text-gray-700 font-medium truncate"><?= $trip['dropoff'][0] ?>, <?= $trip['dropoff'][1] ?></span>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="text-[10px] text-blue-600 font-bold uppercase tracking-widest">View details <i class="fas fa-chevron-right ml-1"></i></span>
                <div class="flex -space-x-2">
                    <div class="w-6 h-6 rounded-full border-2 border-white bg-gray-200"></div>
                    <div class="w-6 h-6 rounded-full border-2 border-white bg-gray-300"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('admin-map', {
        zoomControl: false
    }).setView([14.5995, 120.9842], 12);
    
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '©OpenStreetMap ©CARTO',
        subdomains: 'abcd',
        maxZoom: 20
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
            
            // Remove active class from all cards
            document.querySelectorAll('.trip-card').forEach(c => c.classList.remove('ring-2', 'ring-blue-500', 'border-transparent'));
            this.classList.add('ring-2', 'ring-blue-500', 'border-transparent');

            var lat = parseFloat(this.dataset.lat);
            var lng = parseFloat(this.dataset.lng);
            var pLat = parseFloat(this.dataset.pickupLat);
            var pLng = parseFloat(this.dataset.pickupLng);
            var dLat = parseFloat(this.dataset.dropLat);
            var dLng = parseFloat(this.dataset.dropLng);
            var user = this.dataset.user;

            // Pickup
            markers.push(L.marker([pLat, pLng], {
                icon: L.divIcon({
                    className:'', 
                    html:'<div class="bg-emerald-500 w-4 h-4 rounded-full border-2 border-white shadow-lg shadow-emerald-200"></div>',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(map).bindPopup("Pickup Point"));
            
            // Dropoff
            markers.push(L.marker([dLat, dLng], {
                icon: L.divIcon({
                    className:'', 
                    html:'<div class="bg-rose-500 w-4 h-4 rounded-full border-2 border-white shadow-lg shadow-rose-200"></div>',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                })
            }).addTo(map).bindPopup("Drop-off Point"));
            
            // Current Location
            var carMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div class="relative"><div class="absolute -inset-2 bg-blue-500 opacity-20 rounded-full animate-ping"></div><div class="relative bg-blue-600 p-2.5 rounded-full shadow-xl text-white border-2 border-white"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99z"/></svg></div></div>',
                    iconSize: [40, 40],
                    iconAnchor: [20, 20]
                })
            }).addTo(map).bindPopup("<div class='font-bold'>" + user + "</div><div class='text-xs text-gray-500'>Currently here</div>").openPopup();
            markers.push(carMarker);

            var line = L.polyline([[pLat, pLng], [dLat, dLng]], {
                color: '#2563eb', 
                weight: 4, 
                opacity: 0.2,
                dashArray: '1, 10'
            }).addTo(map);
            lines.push(line);

            map.flyTo([lat, lng], 14, {
                animate: true,
                duration: 1.5
            });
        });
    });

    // Initialize with first trip
    if(document.querySelector('.trip-card')) {
        document.querySelector('.trip-card').click();
    }
</script>

<?php include 'includes/footer.php'; ?>
