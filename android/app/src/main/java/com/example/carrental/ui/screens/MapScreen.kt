package com.example.carrental.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Close
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.rotate
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.compose.ui.viewinterop.AndroidView
import org.osmdroid.config.Configuration
import org.osmdroid.tileprovider.tilesource.TileSourceFactory
import org.osmdroid.util.GeoPoint
import org.osmdroid.views.MapView
import org.osmdroid.views.overlay.Marker
import coil.compose.AsyncImage

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MapScreen(onBackClick: () -> Unit) {
    val context = LocalContext.current
    val mapView = remember { MapView(context) }
    var selectedCar by remember { mutableStateOf<CarLocation?>(null) }

    // Configuration for osmdroid
    LaunchedEffect(Unit) {
        Configuration.getInstance().userAgentValue = context.packageName
    }

    val cars = listOf(
        CarLocation("Luxury Sedan", 14.6010, 120.9850, "https://images.unsplash.com/photo-1552519507-da3b142c6e3d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=60", 50.0),
        CarLocation("Family SUV", 14.5950, 120.9800, "https://images.unsplash.com/photo-1517672651691-24622a91b550?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=60", 70.0),
        CarLocation("Sport Coupe", 14.6050, 120.9900, "https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=60", 90.0),
        CarLocation("Economy Hatchback", 14.6100, 120.9750, "https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=60", 35.0)
    )

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
                factory = {
                    mapView.apply {
                        setTileSource(TileSourceFactory.MAPNIK)
                        setMultiTouchControls(true)
                        controller.setZoom(15.0)
                        controller.setCenter(GeoPoint(14.5995, 120.9842))

                        cars.forEach { car ->
                            val marker = Marker(this)
                            marker.position = GeoPoint(car.lat, car.lng)
                            marker.title = car.name
                            marker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
                            marker.setOnMarkerClickListener { _, _ ->
                                selectedCar = car
                                true
                            }
                            overlays.add(marker)
                        }
                    }
                }
            )

            // Floating Interactive Tile (Card)
            selectedCar?.let { car ->
                Card(
                    modifier = Modifier
                        .align(Alignment.BottomCenter)
                        .padding(16.dp)
                        .fillMaxWidth(),
                    elevation = CardDefaults.cardElevation(defaultElevation = 8.dp)
                ) {
                    Row(
                        modifier = Modifier.padding(16.dp),
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        AsyncImage(
                            model = car.imageUrl,
                            contentDescription = car.name,
                            modifier = Modifier.size(80.dp),
                            contentScale = ContentScale.Crop
                        )
                        Spacer(modifier = Modifier.width(16.dp))
                        Column(modifier = Modifier.weight(1f)) {
                            Text(text = car.name, style = MaterialTheme.typography.titleMedium)
                            Text(text = "$${car.price}/day", style = MaterialTheme.typography.bodyMedium, color = MaterialTheme.colorScheme.primary)
                            Button(
                                onClick = { /* Handle booking */ },
                                modifier = Modifier.padding(top = 8.dp),
                                contentPadding = PaddingValues(horizontal = 12.dp, vertical = 0.dp)
                            ) {
                                Text("Book Now", style = MaterialTheme.typography.labelSmall)
                            }
                        }
                        IconButton(onClick = { selectedCar = null }) {
                            Icon(Icons.Default.Close, contentDescription = "Close")
                        }
                    }
                }
            }
        }
    }
}

data class CarLocation(val name: String, val lat: Double, val lng: Double, val imageUrl: String, val price: Double)
