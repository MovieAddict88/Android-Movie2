package com.fieldservice.agent.ui.screens.settings

import android.content.Context
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.BuildConfig
import com.fieldservice.agent.data.repository.AuthRepository
import com.fieldservice.agent.data.repository.JobLogRepository
import com.fieldservice.agent.worker.SyncWorker
import dagger.hilt.android.lifecycle.HiltViewModel
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.*
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.*
import javax.inject.Inject

@HiltViewModel
class SettingsViewModel @Inject constructor(
    private val authRepository: AuthRepository,
    private val jobLogRepository: JobLogRepository,
    @ApplicationContext private val context: Context
) : ViewModel() {

    private val _uiState = MutableStateFlow(SettingsUiState())
    val uiState: StateFlow<SettingsUiState> = _uiState.asStateFlow()

    init {
        loadPendingSyncs()
    }

    private fun loadPendingSyncs() {
        viewModelScope.launch {
            jobLogRepository.getUnsyncedCount().collect { count ->
                _uiState.update {
                    it.copy(
                        pendingSyncs = count,
                        appVersion = "v${BuildConfig.VERSION_NAME} (${BuildConfig.VERSION_CODE})"
                    )
                }
            }
        }
    }

    fun triggerSync() {
        viewModelScope.launch {
            _uiState.update { it.copy(isSyncing = true) }

            SyncWorker.enqueueSyncWork(context, unique = true)

            kotlinx.coroutines.delay(2000)

            _uiState.update {
                it.copy(
                    isSyncing = false,
                    lastSyncTime = SimpleDateFormat("MMM dd, HH:mm", Locale.US).format(Date())
                )
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            authRepository.clearAuth()
        }
    }
}

data class SettingsUiState(
    val isSyncing: Boolean = false,
    val lastSyncTime: String? = null,
    val pendingSyncs: Int = 0,
    val appVersion: String = ""
)
