package com.example.carrental.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Settings
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.example.carrental.model.Car
import com.example.carrental.viewmodel.CarViewModel
import com.example.carrental.api.RetrofitClient
import com.example.carrental.model.BookingRequest
import kotlinx.coroutines.launch
import java.util.*

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CarListScreen(
    viewModel: CarViewModel,
    modifier: Modifier = Modifier,
    onAdminClick: () -> Unit = {},
    onMapClick: () -> Unit = {},
    onBookClick: (Int, Double) -> Unit = { _, _ -> }
) {
    val cars by viewModel.cars.collectAsState()
    val settings by viewModel.settings.collectAsState()
    val loading by viewModel.loading.collectAsState()
    val scope = rememberCoroutineScope()

    var showBookingDialog by remember { mutableStateOf<Car?>(null) }
    var startDate by remember { mutableStateOf("2024-05-01") }
    var endDate by remember { mutableStateOf("2024-05-03") }
    var withDriver by remember { mutableStateOf(false) }
    var includeCarwash by remember { mutableStateOf(false) }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text(settings?.app_name ?: "Car Rental") },
                actions = {
                    IconButton(onClick = onMapClick) {
                        Icon(Icons.Default.LocationOn, contentDescription = "Map")
                    }
                    IconButton(onClick = onAdminClick) {
                        Icon(Icons.Default.Settings, contentDescription = "Admin")
                    }
                }
            )
        }
    ) { padding ->
        Box(modifier = modifier.fillMaxSize().padding(padding)) {
            if (loading && cars.isEmpty()) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
            } else {
                LazyColumn(
                    contentPadding = PaddingValues(16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    items(cars) { car ->
                        CarItem(car = car, onBookClick = { showBookingDialog = car })
                    }
                }
            }

            showBookingDialog?.let { car ->
                AlertDialog(
                    onDismissRequest = { showBookingDialog = null },
                    title = { Text("Book ${car.brand} ${car.model}") },
                    text = {
                        Column {
                            TextField(value = startDate, onValueChange = { startDate = it }, label = { Text("Start Date (YYYY-MM-DD)") })
                            TextField(value = endDate, onValueChange = { endDate = it }, label = { Text("End Date (YYYY-MM-DD)") })
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Checkbox(checked = withDriver, onCheckedChange = { withDriver = it })
                                Text("With Driver")
                            }
                            Row(verticalAlignment = Alignment.CenterVertically) {
                                Checkbox(checked = includeCarwash, onCheckedChange = { includeCarwash = it })
                                Text("Include Carwash (+${settings?.carwash_amount ?: "0.00"})")
                            }
                        }
                    },
                    confirmButton = {
                        Button(onClick = {
                            scope.launch {
                                try {
                                    val response = RetrofitClient.instance.createBooking(
                                        BookingRequest(
                                            user_id = 2, // Mock user ID for customer
                                            car_id = car.id,
                                            start_date = startDate,
                                            end_date = endDate,
                                            with_driver = if (withDriver) 1 else 0,
                                            include_carwash = if (includeCarwash) 1 else 0
                                        )
                                    )
                                    if (response.isSuccessful) {
                                        // Ideally we'd get the actual booking ID and total amount from the response
                                        // For now, let's navigate to payment. 
                                        // In a real app, the API should return these.
                                        onBookClick(1, car.daily_rate) 
                                    }
                                } catch (e: Exception) {
                                    e.printStackTrace()
                                }
                                showBookingDialog = null
                            }
                        }) {
                            Text("Confirm Booking")
                        }
                    },
                    dismissButton = {
                        TextButton(onClick = { showBookingDialog = null }) { Text("Cancel") }
                    }
                )
            }
        }
    }
}

@Composable
fun CarItem(car: Car, onBookClick: () -> Unit) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Column {
            AsyncImage(
                model = car.image ?: "https://via.placeholder.com/400x250?text=Car+Image",
                contentDescription = null,
                modifier = Modifier
                    .fillMaxWidth()
                    .height(200.dp),
                contentScale = ContentScale.Cover
            )
            Column(modifier = Modifier.padding(16.dp)) {
                Text(
                    text = "${car.brand} ${car.model}",
                    style = MaterialTheme.typography.headlineSmall
                )
                if (car.has_dash_cam == 1) {
                    Text(
                        text = "Dash Cam Included",
                        color = MaterialTheme.colorScheme.secondary,
                        style = MaterialTheme.typography.labelSmall
                    )
                }
                Spacer(modifier = Modifier.height(8.dp))
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Text(text = car.type ?: "Unknown type", style = MaterialTheme.typography.bodyMedium)
                    Text(
                        text = "${car.daily_rate}/day",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.primary
                    )
                }
                Spacer(modifier = Modifier.height(8.dp))
                Button(
                    onClick = onBookClick,
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text("Book Now")
                }
            }
        }
    }
}
