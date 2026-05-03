package com.yourapp.ui

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.yourapp.data.LocationRepository
import com.yourapp.data.entity.JobLocation
import com.yourapp.data.entity.JobLog
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch

class MainViewModel(private val repository: LocationRepository) : ViewModel() {
    val locations: StateFlow<List<JobLocation>> = repository.allLocations
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), emptyList())

    fun completeJob(location: JobLocation, notes: String?) {
        viewModelScope.launch {
            val log = JobLog(
                locationId = location.id,
                checkInAt = System.currentTimeMillis() - 3600000, // Placeholder
                checkOutAt = System.currentTimeMillis(),
                notes = notes,
                isSynced = false
            )
            repository.insertJobLog(log)
        }
    }
}

class MainViewModelFactory(private val repository: LocationRepository) : ViewModelProvider.Factory {
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        if (modelClass.isAssignableFrom(MainViewModel::class.java)) {
            @Suppress("UNCHECKED_CAST")
            return MainViewModel(repository) as T
        }
        throw IllegalArgumentException("Unknown ViewModel class")
    }
}
