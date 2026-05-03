package com.fieldservice.agent.data.remote.model

import com.google.gson.annotations.SerializedName

data class LocationDto(
    val id: Int,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radius: Int,
    val status: String,
    val statusLabel: String,
    val notes: String?,
    val scheduledDate: String?,
    val scheduledTimeStart: String?,
    val scheduledTimeEnd: String?,
    val googleMapsUrl: String?,
    val companyName: String
)

data class JobLogDto(
    val id: Int,
    val syncId: String?,
    val locationId: Int,
    val status: String,
    val startedAt: String?,
    val completedAt: String?,
    val latitudeStart: Double?,
    val longitudeStart: Double?,
    val latitudeEnd: Double?,
    val longitudeEnd: Double?,
    val notes: String?,
    val photoUrl: String?,
    val locationName: String?,
    val locationAddress: String?,
    val durationMinutes: Int?,
    val wasAtLocation: Boolean?
)

data class ApiResponse<T>(
    val success: Boolean,
    val message: String? = null,
    val data: T? = null
)

data class LoginResponse(
    val success: Boolean,
    val message: String? = null,
    val user: UserDto? = null,
    val token: String? = null
)

data class UserDto(
    val id: Int,
    val name: String,
    val email: String,
    val companyId: Int,
    val companyName: String,
    val role: String
)

data class LocationsResponse(
    val success: Boolean,
    val message: String? = null,
    val locations: List<LocationDto>? = null,
    val count: Int = 0
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
    val metadata: Map<String, Any>? = null
)

data class JobLogUploadResponse(
    val success: Boolean,
    val message: String? = null,
    val jobLog: JobLogDto? = null,
    val locationVerified: Boolean = false
)

data class SyncResponse(
    val success: Boolean,
    val syncAt: String?,
    val locations: List<LocationDto>? = null,
    val jobLogs: List<JobLogDto>? = null
)
