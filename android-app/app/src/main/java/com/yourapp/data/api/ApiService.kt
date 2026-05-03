package com.yourapp.data.api

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.Query

interface ApiService {
    @GET("locations")
    suspend fun getLocations(): Response<List<LocationResponse>>

    @POST("job-logs")
    suspend fun uploadJobLog(@Body request: JobLogRequest): Response<JobLogUploadResponse>

    @GET("sync")
    suspend fun sync(@Query("last_sync") lastSync: String?): Response<SyncResponse>
}

data class JobLogUploadResponse(
    val message: String,
    val job_log_id: Long
)

data class SyncResponse(
    val locations: List<LocationResponse>,
    val server_time: String
)
