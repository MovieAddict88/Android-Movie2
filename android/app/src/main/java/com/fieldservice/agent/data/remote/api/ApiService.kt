package com.fieldservice.agent.data.remote.api

import com.fieldservice.agent.data.remote.model.*
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
        @Query("active_only") activeOnly: Boolean = false
    ): Response<LocationsResponse>

    @GET("api/locations/{id}")
    suspend fun getLocation(@Path("id") id: Int): Response<ApiResponse<LocationDto>>

    @PATCH("api/locations/{id}/status")
    suspend fun updateLocationStatus(
        @Path("id") id: Int,
        @Body request: UpdateStatusRequest
    ): Response<ApiResponse<LocationDto>>

    @GET("api/sync")
    suspend fun sync(@Query("last_sync_at") lastSyncAt: String? = null): Response<SyncResponse>

    @POST("api/job-logs")
    suspend fun uploadJobLog(@Body request: JobLogUploadRequest): Response<JobLogUploadResponse>

    @GET("api/job-logs")
    suspend fun getJobLogs(
        @Query("status") status: String? = null,
        @Query("from_date") fromDate: String? = null,
        @Query("to_date") toDate: String? = null,
        @Query("per_page") perPage: Int = 50
    ): Response<ApiResponse<List<JobLogDto>>>
}

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
