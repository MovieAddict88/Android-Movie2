package com.example.carrental.ui.screens

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.unit.dp
import coil.compose.AsyncImage
import com.example.carrental.model.Car
import com.example.carrental.viewmodel.CarViewModel

@Composable
fun CarListScreen(viewModel: CarViewModel, modifier: Modifier = Modifier) {
    val cars by viewModel.cars.collectAsState()
    val loading by viewModel.loading.collectAsState()

    Box(modifier = modifier.fillMaxSize()) {
        if (loading && cars.isEmpty()) {
            CircularProgressIndicator(modifier = Modifier.align(Alignment.Center))
        } else {
            LazyColumn(
                contentPadding = PaddingValues(16.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                items(cars) { car ->
                    CarItem(car = car)
                }
            }
        }
    }
}

@Composable
fun CarItem(car: Car) {
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
                Spacer(modifier = Modifier.height(8.dp))
                Row(
                    modifier = Modifier.fillMaxWidth(),
                    horizontalArrangement = Arrangement.SpaceBetween
                ) {
                    Text(text = car.type ?: "Unknown type", style = MaterialTheme.typography.bodyMedium)
                    Text(
                        text = "$${car.daily_rate}/day",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.primary
                    )
                }
                Spacer(modifier = Modifier.height(8.dp))
                Button(
                    onClick = { /* Navigate to booking */ },
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text("Book Now")
                }
            }
        }
    }
}
