package com.fieldservice.agent.data.repository

import com.fieldservice.agent.data.dao.JobLogDao
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.remote.api.ApiService
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
}
