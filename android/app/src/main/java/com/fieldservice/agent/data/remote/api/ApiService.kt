package com.fieldservice.agent.data.remote.api

import com.google.gson.annotations.SerializedName
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    @POST("api/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("api/logout")
    suspend fun logout(): Response<ApiResponse<Unit>>

    @GET("api/me")
    suspend fun getCurrentUser(): Response<ApiResponse<UserDto>>

    @POST("api/device-token")
    suspend fun updateDeviceToken(@Body request: DeviceTokenRequest): Response<ApiResponse<Unit>>

    @GET("api/locations")
    suspend fun getLocations(
        @Query("status") status: String? = null,
        @Query("date") date: String? = null,
        @Query("active_only") activeOnly: Boolean = false,
        @Query("search") search: String? = null,
        @Query("sort_by") sortBy: String? = null,
        @Query("sort_order") sortOrder: String? = null,
        @Query("per_page") perPage: Int = 50,
        @Query("page") page: Int = 1
    ): Response<LocationsPaginatedResponse>

    @GET("api/locations/{id}")
    suspend fun getLocation(@Path("id") id: Int): Response<ApiResponse<LocationDetailDto>>

    @PATCH("api/locations/{id}/status")
    suspend fun updateLocationStatus(
        @Path("id") id: Int,
        @Body request: UpdateStatusRequest
    ): Response<ApiResponse<LocationStatusDto>>

    @GET("api/sync")
    suspend fun sync(@Query("last_sync_at") lastSyncAt: String? = null): Response<SyncResponse>

    @POST("api/job-logs")
    suspend fun uploadJobLog(@Body request: JobLogUploadRequest): Response<JobLogUploadResponse>

    @GET("api/job-logs")
    suspend fun getJobLogs(
        @Query("status") status: String? = null,
        @Query("from_date") fromDate: String? = null,
        @Query("to_date") toDate: String? = null,
        @Query("per_page") perPage: Int = 50,
        @Query("page") page: Int = 1
    ): Response<JobLogsPaginatedResponse>

    // Notifications API
    @GET("api/notifications")
    suspend fun getNotifications(
        @Query("unread_only") unreadOnly: Boolean = false,
        @Query("type") type: String? = null,
        @Query("per_page") perPage: Int = 20,
        @Query("page") page: Int = 1
    ): Response<NotificationsResponse>

    @PATCH("api/notifications/{id}/read")
    suspend fun markNotificationAsRead(@Path("id") id: Int): Response<ApiResponse<Unit>>

    @POST("api/notifications/mark-all-read")
    suspend fun markAllNotificationsAsRead(): Response<ApiResponse<Unit>>

    @GET("api/notifications/unread-count")
    suspend fun getUnreadNotificationCount(): Response<UnreadCountResponse>

    @DELETE("api/notifications/{id}")
    suspend fun deleteNotification(@Path("id") id: Int): Response<ApiResponse<Unit>>
}

// Request Models
data class LoginRequest(
    val email: String,
    val password: String,
    val deviceToken: String? = null,
    val deviceName: String? = null,
    val deviceType: String = "android"
)

data class DeviceTokenRequest(
    val deviceToken: String,
    val deviceName: String? = null,
    val deviceType: String = "android"
)

data class UpdateStatusRequest(
    val status: String
)

// API Response wrapper
data class ApiResponse<T>(
    val success: Boolean,
    val message: String? = null,
    val data: T? = null
)

// Login Response
data class LoginResponse(
    val success: Boolean,
    val message: String? = null,
    val user: UserData? = null,
    val token: String? = null,
    val tokenType: String? = null
)

data class UserData(
    val id: Int,
    val name: String,
    val email: String,
    val phone: String? = null,
    val companyId: Int,
    val companyName: String?,
    val role: String
)

// Location DTOs
data class LocationDto(
    val id: Int,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radius: Int,
    val status: String,
    val statusLabel: String?,
    val notes: String?,
    val scheduledDate: String?,
    val scheduledTimeStart: String?,
    val scheduledTimeEnd: String?,
    val googleMapsUrl: String?,
    val companyName: String?
)

data class LocationDetailDto(
    val id: Int,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radius: Int,
    val status: String,
    val statusLabel: String?,
    val notes: String?,
    val scheduledDate: String?,
    val scheduledTimeStart: String?,
    val scheduledTimeEnd: String?,
    val googleMapsUrl: String?,
    val metadata: Map<String, Any>?,
    val company: CompanyDto?,
    val assignedWorker: WorkerDto?,
    val recentJobs: List<RecentJobDto>?,
    val createdAt: String?,
    val updatedAt: String?
)

data class CompanyDto(
    val id: Int,
    val name: String
)

data class WorkerDto(
    val id: Int,
    val name: String,
    val phone: String?
)

data class RecentJobDto(
    val id: Int,
    val status: String,
    val statusLabel: String?,
    val completedAt: String?,
    val notes: String?
)

data class LocationStatusDto(
    val id: Int,
    val status: String,
    val statusLabel: String?,
    val updatedAt: String?
)

// Pagination response
data class LocationsPaginatedResponse(
    val success: Boolean,
    val message: String? = null,
    val locations: List<LocationDto>?,
    val pagination: PaginationDto?
)

data class JobLogsPaginatedResponse(
    val success: Boolean,
    val message: String? = null,
    val jobLogs: List<JobLogDto>?,
    val pagination: PaginationDto?
)

data class PaginationDto(
    val currentPage: Int,
    val lastPage: Int,
    val perPage: Int,
    val total: Int,
    val hasMore: Boolean
)

// Legacy response (for backward compatibility)
@Deprecated("Use LocationsPaginatedResponse instead")
data class LocationsResponse(
    val success: Boolean,
    val message: String? = null,
    val locations: List<LocationDto>? = null,
    val count: Int? = null
)

// Sync Response
data class SyncResponse(
    val success: Boolean,
    val message: String? = null,
    val syncAt: String?,
    val locationsCount: Int?,
    val jobLogsCount: Int?,
    val locations: List<LocationDto>?,
    val jobLogs: List<JobLogDto>?
)

// Job Log DTOs
data class JobLogDto(
    val id: Int,
    val locationId: Int,
    val locationName: String?,
    val locationAddress: String?,
    val syncId: String?,
    val status: String,
    val statusLabel: String?,
    val startedAt: String?,
    val completedAt: String?,
    val durationMinutes: Int?,
    val notes: String?,
    val photoUrl: String?,
    val signatureUrl: String?,
    val wasAtLocation: Boolean?
)

data class JobLogUploadRequest(
    val locationId: Int,
    val syncId: String,
    val status: String,
    val startedAt: String,
    val completedAt: String,
    val latitudeStart: Double?,
    val longitudeStart: Double?,
    val latitudeEnd: Double?,
    val longitudeEnd: Double?,
    val notes: String?,
    val photoBase64: String?,
    val metadata: Map<String, Any>?
)

data class JobLogUploadResponse(
    val success: Boolean,
    val message: String? = null,
    val jobLog: UploadedJobLogDto?,
    val locationVerified: Boolean?,
    val verificationDistanceMeters: Double?
)

data class UploadedJobLogDto(
    val id: Int,
    val syncId: String,
    val status: String,
    val startedAt: String?,
    val completedAt: String?,
    val photoUrl: String?
)

// User DTO for /me endpoint
data class UserDto(
    val id: Int,
    val name: String,
    val email: String,
    val phone: String?,
    val companyId: Int,
    val companyName: String?,
    val role: String,
    val lastActiveAt: String?
)

// Notification DTOs
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

data class NotificationsResponse(
    val success: Boolean,
    val message: String? = null,
    val notifications: List<NotificationDto>?,
    val pagination: NotificationPaginationDto?
)

data class NotificationPaginationDto(
    val currentPage: Int,
    val lastPage: Int,
    val perPage: Int,
    val total: Int,
    val unreadCount: Int
)

data class UnreadCountResponse(
    val success: Boolean,
    val unreadCount: Int
)