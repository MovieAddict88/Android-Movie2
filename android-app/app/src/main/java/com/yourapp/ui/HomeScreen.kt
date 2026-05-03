package com.yourapp.ui

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.yourapp.data.entity.JobLocation

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun HomeScreen(locations: List<JobLocation>, onCompleteJob: (JobLocation) -> Unit) {
    Scaffold(
        topBar = {
            TopAppBar(title = { Text("Field Service Jobs") })
        }
    ) { padding ->
        LazyColumn(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
        ) {
            items(locations) { location ->
                JobItem(location, onCompleteJob)
            }
        }
    }
}

@Composable
fun JobItem(location: JobLocation, onCompleteJob: (JobLocation) -> Unit) {
    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 8.dp),
        elevation = CardDefaults.cardElevation(defaultElevation = 4.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Text(text = location.name, style = MaterialTheme.typography.titleLarge)
            Text(text = location.address, style = MaterialTheme.typography.bodyMedium)
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = "Status: Available",
                style = MaterialTheme.typography.labelMedium,
                color = MaterialTheme.colorScheme.secondary
            )
            Spacer(modifier = Modifier.height(8.dp))
            Button(onClick = { onCompleteJob(location) }) {
                Text("Complete Job")
            }
        }
    }
}
