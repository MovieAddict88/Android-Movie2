package com.fieldservice.agent.ui.screens.notifications

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.NotificationDto
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class NotificationsViewModel @Inject constructor(
    private val apiService: ApiService
) : ViewModel() {

    private val _uiState = MutableStateFlow(NotificationsUiState())
    val uiState: StateFlow<NotificationsUiState> = _uiState.asStateFlow()

    init {
        loadNotifications()
    }

    fun loadNotifications() {
        viewModelScope.launch {
            _uiState.update { it.copy(isLoading = true, error = null) }

            try {
                val response = apiService.getNotifications(
                    perPage = 50,
                    page = 1
                )

                if (response.success && response.notifications != null) {
                    _uiState.update {
                        it.copy(
                            isLoading = false,
                            notifications = response.notifications,
                            unreadCount = response.pagination?.unreadCount ?: 0
                        )
                    }
                } else {
                    _uiState.update {
                        it.copy(
                            isLoading = false,
                            error = response.message ?: "Failed to load notifications"
                        )
                    }
                }
            } catch (e: Exception) {
                _uiState.update {
                    it.copy(
                        isLoading = false,
                        error = e.message ?: "Network error"
                    )
                }
            }
        }
    }

    fun markAsRead(notificationId: Int) {
        viewModelScope.launch {
            try {
                val response = apiService.markNotificationAsRead(notificationId)
                if (response.success) {
                    _uiState.update { state ->
                        state.copy(
                            notifications = state.notifications.map { notification ->
                                if (notification.id == notificationId) {
                                    notification.copy(isRead = true)
                                } else {
                                    notification
                                }
                            },
                            unreadCount = (state.unreadCount - 1).coerceAtLeast(0)
                        )
                    }
                }
            } catch (e: Exception) {
                // Silently fail
            }
        }
    }

    fun markAllAsRead() {
        viewModelScope.launch {
            try {
                val response = apiService.markAllNotificationsAsRead()
                if (response.success) {
                    _uiState.update { state ->
                        state.copy(
                            notifications = state.notifications.map { it.copy(isRead = true) },
                            unreadCount = 0
                        )
                    }
                }
            } catch (e: Exception) {
                // Silently fail
            }
        }
    }

    fun deleteNotification(notificationId: Int) {
        viewModelScope.launch {
            try {
                val response = apiService.deleteNotification(notificationId)
                if (response.success) {
                    val notification = _uiState.value.notifications.find { it.id == notificationId }
                    _uiState.update { state ->
                        state.copy(
                            notifications = state.notifications.filter { it.id != notificationId },
                            unreadCount = if (notification?.isRead == false) {
                                (state.unreadCount - 1).coerceAtLeast(0)
                            } else {
                                state.unreadCount
                            }
                        )
                    }
                }
            } catch (e: Exception) {
                // Silently fail
            }
        }
    }

    fun refresh() {
        loadNotifications()
    }

    fun clearError() {
        _uiState.update { it.copy(error = null) }
    }
}

data class NotificationsUiState(
    val isLoading: Boolean = false,
    val notifications: List<NotificationDto> = emptyList(),
    val unreadCount: Int = 0,
    val error: String? = null
)

data class NotificationDto(
    val id: Int,
    val type: String,
    val title: String,
    val body: String,
    val data: Map<String, Any>?,
    val status: String,
    val isRead: Boolean,
    val createdAt: String?,
    val sentAt: String?
)