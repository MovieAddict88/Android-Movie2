package com.fieldservice.agent.ui.screens.camera

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.data.repository.JobLogRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CameraViewModel @Inject constructor(
    private val jobLogRepository: JobLogRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow(CameraUiState())
    val uiState: StateFlow<CameraUiState> = _uiState.asStateFlow()

    fun savePhoto(locationId: Int, photoBase64: String) {
        viewModelScope.launch {
            _uiState.update { it.copy(isSaving = true) }

            try {
                val currentJobLogId = getActiveJobLogId(locationId)
                if (currentJobLogId != null) {
                    jobLogRepository.updatePhoto(currentJobLogId, photoBase64)
                }
                _uiState.update { it.copy(isSaving = false, savedSuccessfully = true) }
            } catch (e: Exception) {
                _uiState.update { it.copy(isSaving = false, error = e.message) }
            }
        }
    }

    private suspend fun getActiveJobLogId(locationId: Int): Int? {
        val jobLogs = jobLogRepository.getJobLogsByLocation(locationId)
        return null
    }

    fun clearState() {
        _uiState.update { CameraUiState() }
    }
}

data class CameraUiState(
    val isSaving: Boolean = false,
    val savedSuccessfully: Boolean = false,
    val error: String? = null
)
