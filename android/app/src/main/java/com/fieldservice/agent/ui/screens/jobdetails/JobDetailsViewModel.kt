package com.fieldservice.agent.ui.screens.jobdetails

import android.content.Context
import android.util.Log
import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.entity.LocationEntity
import com.fieldservice.agent.data.repository.JobLogRepository
import com.fieldservice.agent.data.repository.LocationRepository
import com.fieldservice.agent.worker.SyncWorker
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch
import java.util.*
import javax.inject.Inject

@HiltViewModel
class JobDetailsViewModel @Inject constructor(
    savedStateHandle: SavedStateHandle,
    private val locationRepository: LocationRepository,
    private val jobLogRepository: JobLogRepository,
    @ApplicationContext private val context: Context
) : ViewModel() {

    private val locationId: Int = savedStateHandle.get<Int>("locationId") ?: 0

    private val _uiState = MutableStateFlow(JobDetailsUiState())
    val uiState: StateFlow<JobDetailsUiState> = _uiState.asStateFlow()

    private val _events = MutableSharedFlow<JobDetailsEvent>()
    val events: SharedFlow<JobDetailsEvent> = _events.asSharedFlow()

    init {
        loadLocation()
    }

    private fun loadLocation() {
        viewModelScope.launch {
            _uiState.update { it.copy(isLoading = true) }

            locationRepository.getLocationById(locationId)
                .catch { e ->
                    _uiState.update { it.copy(isLoading = false, error = e.message) }
                }
                .collect { location ->
                    _uiState.update {
                        it.copy(
                            isLoading = false,
                            location = location,
                            error = null
                        )
                    }
                }
        }
    }

    fun startJob(currentLat: Double, currentLng: Double) {
        viewModelScope.launch {
            _uiState.update { it.copy(isWorking = true) }

            val syncId = UUID.randomUUID().toString()
            val jobLog = JobLogEntity(
                syncId = syncId,
                locationId = locationId,
                locationName = _uiState.value.location?.name ?: "",
                locationAddress = _uiState.value.location?.address ?: "",
                status = "in_progress",
                startedAt = System.currentTimeMillis(),
                completedAt = null,
                latitudeStart = currentLat,
                longitudeStart = currentLng,
                latitudeEnd = null,
                longitudeEnd = null,
                notes = null,
                photoBase64 = null,
                isSynced = false
            )

            try {
                val id = jobLogRepository.insertJobLog(jobLog)
                locationRepository.updateLocationStatus(locationId, "in_progress")
                SyncWorker.enqueueSyncWork(context, unique = true)

                _uiState.update {
                    it.copy(
                        isWorking = false,
                        currentJobLogId = id.toInt(),
                        currentSyncId = syncId
                    )
                }
                _events.emit(JobDetailsEvent.JobStarted)
            } catch (e: Exception) {
                _uiState.update { it.copy(isWorking = false, error = e.message) }
            }
        }
    }

    fun completeJob(
        currentLat: Double,
        currentLng: Double,
        photoBase64: String?,
        notes: String?
    ) {
        viewModelScope.launch {
            _uiState.update { it.copy(isWorking = true) }

            val currentJobLogId = _uiState.value.currentJobLogId
            val syncId = _uiState.value.currentSyncId ?: UUID.randomUUID().toString()

            try {
                if (currentJobLogId != null) {
                    val existingLog = jobLogRepository.getJobLogById(currentJobLogId)
                    if (existingLog != null) {
                        val updatedLog = existingLog.copy(
                            status = "completed",
                            completedAt = System.currentTimeMillis(),
                            latitudeEnd = currentLat,
                            longitudeEnd = currentLng,
                            notes = notes,
                            photoBase64 = photoBase64,
                            isSynced = false
                        )
                        jobLogRepository.updateJobLog(updatedLog)
                    }
                } else {
                    val jobLog = JobLogEntity(
                        syncId = syncId,
                        locationId = locationId,
                        locationName = _uiState.value.location?.name ?: "",
                        locationAddress = _uiState.value.location?.address ?: "",
                        status = "completed",
                        startedAt = System.currentTimeMillis(),
                        completedAt = System.currentTimeMillis(),
                        latitudeStart = currentLat,
                        longitudeStart = currentLng,
                        latitudeEnd = currentLat,
                        longitudeEnd = currentLng,
                        notes = notes,
                        photoBase64 = photoBase64,
                        isSynced = false
                    )
                    jobLogRepository.insertJobLog(jobLog)
                }

                locationRepository.updateLocationStatus(locationId, "completed")
                SyncWorker.enqueueSyncWork(context, unique = true)

                _uiState.update {
                    it.copy(
                        isWorking = false,
                        currentJobLogId = null,
                        currentSyncId = null
                    )
                }
                _events.emit(JobDetailsEvent.JobCompleted)
                loadLocation()
            } catch (e: Exception) {
                _uiState.update { it.copy(isWorking = false, error = e.message) }
            }
        }
    }

    fun cancelJob() {
        viewModelScope.launch {
            val currentJobLogId = _uiState.value.currentJobLogId

            if (currentJobLogId != null) {
                jobLogRepository.deleteJobLogById(currentJobLogId)
            }

            locationRepository.updateLocationStatus(locationId, "pending")
            _uiState.update {
                it.copy(
                    currentJobLogId = null,
                    currentSyncId = null
                )
            }
            _events.emit(JobDetailsEvent.JobCancelled)
        }
    }

    fun clearError() {
        _uiState.update { it.copy(error = null) }
    }
}

data class JobDetailsUiState(
    val isLoading: Boolean = true,
    val isWorking: Boolean = false,
    val location: LocationEntity? = null,
    val currentJobLogId: Int? = null,
    val currentSyncId: String? = null,
    val error: String? = null
)

sealed class JobDetailsEvent {
    data object JobStarted : JobDetailsEvent()
    data object JobCompleted : JobDetailsEvent()
    data object JobCancelled : JobDetailsEvent()
}
