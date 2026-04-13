package com.example.carrental.ui.screens

import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.viewinterop.AndroidView

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MapScreen(onBackClick: () -> Unit) {
    val htmlContent = """
        <!DOCTYPE html>
        <html>
        <head>
            <title>Leaflet Map</title>
            <meta charset="utf-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            <style>
                body { padding: 0; margin: 0; }
                html, body, #map { height: 100%; width: 100vw; }
            </style>
        </head>
        <body>
            <div id="map"></div>
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                var map = L.map('map').setView([14.5995, 120.9842], 13);

                var osm = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                });

                var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Esri'
                });

                osm.addTo(map);

                var baseMaps = {
                    "Map": osm,
                    "Satellite": satellite
                };
                L.control.layers(baseMaps).addTo(map);

                // Add some dummy car locations
                var cars = [
                    { name: "Luxury Sedan", lat: 14.6010, lng: 120.9850 },
                    { name: "Family SUV", lat: 14.5950, lng: 120.9800 },
                    { name: "Sport Coupe", lat: 14.6050, lng: 120.9900 },
                    { name: "Economy Hatchback", lat: 14.6100, lng: 120.9750 }
                ];

                cars.forEach(function(car) {
                    L.marker([car.lat, car.lng])
                        .addTo(map)
                        .bindPopup('<b>' + car.name + '</b><br>Available for rent');
                });
            </script>
        </body>
        </html>
    """.trimIndent()

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Car Locations") },
                navigationIcon = {
                    IconButton(onClick = onBackClick) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Back")
                    }
                }
            )
        }
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            AndroidView(
                modifier = Modifier.fillMaxSize(),
                factory = { context ->
                    WebView(context).apply {
                        webViewClient = WebViewClient()
                        settings.javaScriptEnabled = true
                        settings.domStorageEnabled = true
                        loadDataWithBaseURL("https://appassets.androidplatform.net", htmlContent, "text/html", "UTF-8", null)
                    }
                }
            )
        }
    }
}
