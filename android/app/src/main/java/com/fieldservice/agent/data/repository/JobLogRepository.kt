package com.fieldservice.agent.data.repository

import com.fieldservice.agent.data.dao.JobLogDao
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.JobLogUploadRequest
import com.fieldservice.agent.util.NetworkResult
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class JobLogRepository @Inject constructor(
    private val jobLogDao: JobLogDao,
    private val apiService: ApiService
) {
    fun getAllJobLogs(): Flow<List<JobLogEntity>> = jobLogDao.getAllJobLogs()

    fun getJobLogsByLocation(locationId: Int): Flow<List<JobLogEntity>> =
        jobLogDao.getJobLogsByLocation(locationId)

    suspend fun getUnsyncedJobLogs(): List<JobLogEntity> = jobLogDao.getUnsyncedJobLogs()

    suspend fun getJobLogById(id: Int): JobLogEntity? = jobLogDao.getJobLogById(id)

    suspend fun insertJobLog(jobLog: JobLogEntity): Long = jobLogDao.insertJobLog(jobLog)

    suspend fun updateJobLog(jobLog: JobLogEntity) = jobLogDao.updateJobLog(jobLog)

    suspend fun markAsSynced(id: Int) = jobLogDao.markAsSynced(id)

    suspend fun deleteJobLog(jobLog: JobLogEntity) = jobLogDao.deleteJobLog(jobLog)

    suspend fun deleteJobLogById(id: Int) = jobLogDao.deleteJobLogById(id)

    suspend fun updatePhoto(jobLogId: Int, photoBase64: String) {
        val jobLog = jobLogDao.getJobLogById(jobLogId)
        if (jobLog != null) {
            jobLogDao.updateJobLog(jobLog.copy(photoBase64 = photoBase64))
        }
    }

    fun getUnsyncedCount(): Flow<Int> = jobLogDao.getUnsyncedCount()

    fun getCompletedCountSince(startTime: Long): Flow<Int> =
        jobLogDao.getCompletedCountSince(startTime)

    suspend fun clearAllJobLogs() {
        jobLogDao.deleteAllJobLogs()
    }

    suspend fun uploadJobLog(jobLog: JobLogEntity): NetworkResult<JobLogUploadResponse> {
        return try {
            val dateFormat = java.text.SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'", java.util.Locale.US).apply {
                timeZone = java.util.TimeZone.getTimeZone("UTC")
            }

            val request = JobLogUploadRequest(
                locationId = jobLog.locationId,
                syncId = jobLog.syncId,
                status = jobLog.status,
                startedAt = dateFormat.format(java.util.Date(jobLog.startedAt)),
                completedAt = jobLog.completedAt?.let { dateFormat.format(java.util.Date(it)) } ?: "",
                latitudeStart = jobLog.latitudeStart,
                longitudeStart = jobLog.longitudeStart,
                latitudeEnd = jobLog.latitudeEnd,
                longitudeEnd = jobLog.longitudeEnd,
                notes = jobLog.notes,
                photoBase64 = jobLog.photoBase64,
                metadata = null
            )

            val response = apiService.uploadJobLog(request)
            if (response.isSuccessful && response.body()?.success == true) {
                markAsSynced(jobLog.id)
                NetworkResult.Success(response.body()!!)
            } else {
                NetworkResult.Error(response.body()?.message ?: "Upload failed")
            }
        } catch (e: Exception) {
            NetworkResult.Error(e.message ?: "Network error")
        }
    }

    suspend fun getJobLogsFromServer(
        status: String? = null,
        fromDate: String? = null,
        toDate: String? = null,
        page: Int = 1,
        perPage: Int = 50
    ): NetworkResult<List<JobLogEntity>> {
        return try {
            val response = apiService.getJobLogs(status, fromDate, toDate, perPage, page)
            if (response.success && response.jobLogs != null) {
                val entities = response.jobLogs.map { dto ->
                    JobLogEntity(
                        id = dto.id,
                        locationId = dto.locationId,
                        locationName = dto.locationName ?: "",
                        locationAddress = dto.locationAddress ?: "",
                        syncId = dto.syncId ?: "",
                        status = dto.status,
                        startedAt = 0,
                        completedAt = null,
                        latitudeStart = null,
                        longitudeStart = null,
                        latitudeEnd = null,
                        longitudeEnd = null,
                        notes = dto.notes,
                        photoBase64 = null,
                        isSynced = true
                    )
                }
                NetworkResult.Success(entities)
            } else {
                NetworkResult.Error(response.message ?: "Failed to fetch job logs")
            }
        } catch (e: Exception) {
            NetworkResult.Error(e.message ?: "Network error")
        }
    }
}

data class JobLogUploadResponse(
    val id: Int,
    val syncId: String,
    val status: String,
    val photoUrl: String?
)