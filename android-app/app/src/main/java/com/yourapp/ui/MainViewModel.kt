package com.yourapp.ui

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.viewModelScope
import com.yourapp.data.LocationRepository
import com.yourapp.data.entity.JobLocation
import com.yourapp.data.entity.JobLog
import com.yourapp.util.PdfGenerator
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch

class MainViewModel(application: Application, private val repository: LocationRepository) : AndroidViewModel(application) {
    val locations: StateFlow<List<JobLocation>> = repository.allLocations
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), emptyList())

    private val _pendingLocation = MutableStateFlow<JobLocation?>(null)
    val pendingLocation = _pendingLocation.asStateFlow()

    private val pdfGenerator = PdfGenerator(application)

    fun setPendingLocation(location: JobLocation?) {
        _pendingLocation.value = location
    }

    fun completeJob(location: JobLocation, notes: String?) {
        viewModelScope.launch {
            val log = JobLog(
                locationId = location.id,
                checkInAt = System.currentTimeMillis() - 3600000,
                checkOutAt = System.currentTimeMillis(),
                notes = notes,
                isSynced = false
            )
            val logId = repository.insertJobLog(log)
            val savedLog = log.copy(id = logId)

            // Generate PDF Report
            pdfGenerator.generateReport(savedLog, location.name)

            _pendingLocation.value = null
        }
    }
}

class MainViewModelFactory(private val application: Application, private val repository: LocationRepository) : ViewModelProvider.Factory {
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        if (modelClass.isAssignableFrom(MainViewModel::class.java)) {
            @Suppress("UNCHECKED_CAST")
            return MainViewModel(application, repository) as T
        }
        throw IllegalArgumentException("Unknown ViewModel class")
    }
}
