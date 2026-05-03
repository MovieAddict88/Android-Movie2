package com.fieldservice.agent.ui.screens.home

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.data.entity.LocationEntity
import com.fieldservice.agent.data.repository.LocationRepository
import com.fieldservice.agent.service.GeofenceEventManager
import com.fieldservice.agent.service.GeofenceHelper
import com.fieldservice.agent.service.GeofenceListener
import com.fieldservice.agent.worker.SyncWorker
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*
import javax.inject.Inject

@HiltViewModel
class HomeViewModel @Inject constructor(
    private val locationRepository: LocationRepository,
    private val geofenceHelper: GeofenceHelper
) : ViewModel(), GeofenceListener {

    private val _uiState = MutableStateFlow(HomeUiState())
    val uiState: StateFlow<HomeUiState> = _uiState.asStateFlow()

    private val todayDate = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date())

    init {
        loadLocations()
        setupGeofenceListener()
        observeGeofenceStatus()
    }

    private fun loadLocations() {
        viewModelScope.launch {
            _uiState.update { it.copy(isLoading = true) }

            locationRepository.getTodayLocations(todayDate)
                .catch { e ->
                    _uiState.update { 
                        it.copy(
                            isLoading = false, 
                            error = e.message ?: "Failed to load locations"
                        )
                    }
                }
                .collect { locations ->
                    _uiState.update {
                        it.copy(
                            isLoading = false,
                            locations = locations,
                            error = null
                        )
                    }
                    setupGeofences(locations)
                }
        }
    }

    private fun setupGeofenceListener() {
        GeofenceEventManager.addListener(this)
    }

    private fun observeGeofenceStatus() {
        viewModelScope.launch {
            val hasPermission = geofenceHelper.hasLocationPermission()
            _uiState.update { it.copy(hasLocationPermission = hasPermission) }
        }
    }

    private fun setupGeofences(locations: List<LocationEntity>) {
        if (!geofenceHelper.hasLocationPermission()) {
            return
        }

        geofenceHelper.addGeofences(
            locations = locations,
            onSuccess = {
                _uiState.update { it.copy(geofenceEnabled = true) }
            },
            onFailure = { e ->
                _uiState.update { it.copy(geofenceEnabled = false) }
            }
        )
    }

    fun refreshLocations() {
        viewModelScope.launch {
            _uiState.update { it.copy(isRefreshing = true, error = null) }
            
            locationRepository.syncLocations()
                .onSuccess { locations ->
                    _uiState.update { 
                        it.copy(
                            isRefreshing = false,
                            locations = locations,
                            error = null
                        )
                    }
                }
                .onFailure { e ->
                    _uiState.update { 
                        it.copy(
                            isRefreshing = false,
                            error = e.message ?: "Failed to refresh"
                        )
                    }
                }
        }
    }

    fun onEnteredGeofence(locationId: Int) {
        viewModelScope.launch {
            val currentLocations = _uiState.value.locations
            val updatedLocations = currentLocations.map { location ->
                if (location.id == locationId) {
                    location.copy(status = "in_progress")
                } else {
                    location
                }
            }
            _uiState.update { it.copy(locations = updatedLocations) }

            if (geofenceHelper.hasLocationPermission()) {
                locationRepository.updateLocationStatus(locationId, "in_progress")
            }
        }
    }

    fun onExitedGeofence(locationId: Int) {
        viewModelScope.launch {
            locationRepository.updateLocationStatus(locationId, "pending")
        }
    }

    fun clearError() {
        _uiState.update { it.copy(error = null) }
    }

    override fun onCleared() {
        super.onCleared()
        GeofenceEventManager.removeListener(this)
    }
}

data class HomeUiState(
    val isLoading: Boolean = true,
    val isRefreshing: Boolean = false,
    val locations: List<LocationEntity> = emptyList(),
    val geofenceEnabled: Boolean = false,
    val hasLocationPermission: Boolean = false,
    val error: String? = null
)