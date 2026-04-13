package com.example.carrental.ui.screens

import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.Close
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.PlayArrow
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import org.osmdroid.config.Configuration
import org.osmdroid.events.MapEventsReceiver
import org.osmdroid.tileprovider.tilesource.TileSourceFactory
import org.osmdroid.util.GeoPoint
import org.osmdroid.views.MapView
import org.osmdroid.views.overlay.MapEventsOverlay
import org.osmdroid.views.overlay.Marker
import org.osmdroid.views.overlay.Polyline
import coil.compose.AsyncImage
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch

enum class TripStatus {
    IDLE, SELECTING_PICKUP, SELECTING_DROPOFF, READY, TRACKING, ARRIVED
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun MapScreen(onBackClick: () -> Unit) {
    val context = LocalContext.current
    val scope = rememberCoroutineScope()
    val mapView = remember { MapView(context) }
    
    var tripStatus by remember { mutableStateOf(TripStatus.IDLE) }
    var pickupPoint by remember { mutableStateOf<GeoPoint?>(null) }
    var dropoffPoint by remember { mutableStateOf<GeoPoint?>(null) }
    var currentCarPos by remember { mutableStateOf<GeoPoint?>(null) }
    
    val pickupMarker = remember { Marker(mapView) }
    val dropoffMarker = remember { Marker(mapView) }
    val carMarker = remember { Marker(mapView) }
    val tripPolyline = remember { Polyline() }

    // Configuration for osmdroid
    LaunchedEffect(Unit) {
        Configuration.getInstance().userAgentValue = context.packageName
        
        pickupMarker.title = "Pickup Point"
        pickupMarker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
        
        dropoffMarker.title = "Dropoff Point"
        dropoffMarker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_BOTTOM)
        
        carMarker.title = "Car"
        carMarker.setAnchor(Marker.ANCHOR_CENTER, Marker.ANCHOR_CENTER)
        
        tripPolyline.outlinePaint.color = android.graphics.Color.BLUE
        tripPolyline.outlinePaint.strokeWidth = 10f
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Interactive GPS Tracker") },
                navigationIcon = {
                    IconButton(onClick = onBackClick) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Back")
                    }
                },
                actions = {
                    if (tripStatus != TripStatus.IDLE) {
                        IconButton(onClick = {
                            tripStatus = TripStatus.IDLE
                            pickupPoint = null
                            dropoffPoint = null
                            currentCarPos = null
                            mapView.overlays.remove(pickupMarker)
                            mapView.overlays.remove(dropoffMarker)
                            mapView.overlays.remove(carMarker)
                            mapView.overlays.remove(tripPolyline)
                            mapView.invalidate()
                        }) {
                            Icon(Icons.Default.Refresh, contentDescription = "Reset")
                        }
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

                        val eventsOverlay = MapEventsOverlay(object : MapEventsReceiver {
                            override fun singleTapConfirmedHelper(p: GeoPoint?): Boolean {
                                p?.let { point ->
                                    when (tripStatus) {
                                        TripStatus.IDLE, TripStatus.SELECTING_PICKUP -> {
                                            pickupPoint = point
                                            pickupMarker.position = point
                                            if (!overlays.contains(pickupMarker)) overlays.add(pickupMarker)
                                            tripStatus = TripStatus.SELECTING_DROPOFF
                                            invalidate()
                                        }
                                        TripStatus.SELECTING_DROPOFF -> {
                                            dropoffPoint = point
                                            dropoffMarker.position = point
                                            if (!overlays.contains(dropoffMarker)) overlays.add(dropoffMarker)
                                            
                                            tripPolyline.setPoints(listOf(pickupPoint, dropoffPoint))
                                            if (!overlays.contains(tripPolyline)) overlays.add(tripPolyline)
                                            
                                            tripStatus = TripStatus.READY
                                            invalidate()
                                        }
                                        else -> {}
                                    }
                                }
                                return true
                            }

                            override fun longPressHelper(p: GeoPoint?): Boolean = false
                        })
                        overlays.add(eventsOverlay)
                    }
                }
            )

            // Overlay UI
            Card(
                modifier = Modifier
                    .align(Alignment.TopCenter)
                    .padding(16.dp)
                    .fillMaxWidth(),
                colors = CardDefaults.cardColors(containerColor = Color.White.copy(alpha = 0.9f)),
                elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Text(
                        text = when (tripStatus) {
                            TripStatus.IDLE -> "Tap on map to set Pickup"
                            TripStatus.SELECTING_PICKUP -> "Tap on map to set Pickup"
                            TripStatus.SELECTING_DROPOFF -> "Tap on map to set Dropoff"
                            TripStatus.READY -> "Ready to start trip"
                            TripStatus.TRACKING -> "Trip in progress..."
                            TripStatus.ARRIVED -> "Arrived at destination!"
                        },
                        style = MaterialTheme.typography.titleMedium,
                        fontWeight = FontWeight.Bold
                    )
                    
                    if (tripStatus == TripStatus.READY) {
                        Button(
                            onClick = {
                                tripStatus = TripStatus.TRACKING
                                scope.launch {
                                    val start = pickupPoint!!
                                    val end = dropoffPoint!!
                                    val steps = 50
                                    carMarker.position = start
                                    if (!mapView.overlays.contains(carMarker)) mapView.overlays.add(carMarker)
                                    
                                    for (i in 0..steps) {
                                        val lat = start.latitude + (end.latitude - start.latitude) * (i.toDouble() / steps)
                                        val lng = start.longitude + (end.longitude - start.longitude) * (i.toDouble() / steps)
                                        val nextPos = GeoPoint(lat, lng)
                                        currentCarPos = nextPos
                                        carMarker.position = nextPos
                                        mapView.invalidate()
                                        delay(100)
                                    }
                                    tripStatus = TripStatus.ARRIVED
                                }
                            },
                            modifier = Modifier.padding(top = 8.dp).fillMaxWidth()
                        ) {
                            Icon(Icons.Default.PlayArrow, contentDescription = null)
                            Spacer(Modifier.width(8.dp))
                            Text("Start Tracking Simulation")
                        }
                    }

                    if (tripStatus == TripStatus.TRACKING || tripStatus == TripStatus.ARRIVED) {
                        LinearProgressIndicator(
                            progress = if (tripStatus == TripStatus.ARRIVED) 1f else 0.5f, // simplified
                            modifier = Modifier.fillMaxWidth().padding(vertical = 8.dp)
                        )
                    }
                }
            }
        }
    }
}
