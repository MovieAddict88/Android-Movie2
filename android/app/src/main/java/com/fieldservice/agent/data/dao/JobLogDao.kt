package com.fieldservice.agent.data.dao

import androidx.room.*
import com.fieldservice.agent.data.entity.JobLogEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface JobLogDao {

    @Query("SELECT * FROM job_logs ORDER BY completedAt DESC")
    fun getAllJobLogs(): Flow<List<JobLogEntity>>

    @Query("SELECT * FROM job_logs WHERE isSynced = 0")
    suspend fun getUnsyncedJobLogs(): List<JobLogEntity>

    @Query("SELECT * FROM job_logs WHERE locationId = :locationId ORDER BY completedAt DESC")
    fun getJobLogsByLocation(locationId: Int): Flow<List<JobLogEntity>>

    @Query("SELECT * FROM job_logs WHERE syncId = :syncId")
    suspend fun getJobLogBySyncId(syncId: String): JobLogEntity?

    @Query("SELECT * FROM job_logs WHERE id = :id")
    suspend fun getJobLogById(id: Int): JobLogEntity?

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertJobLog(jobLog: JobLogEntity): Long

    @Update
    suspend fun updateJobLog(jobLog: JobLogEntity)

    @Query("UPDATE job_logs SET isSynced = 1 WHERE id = :id")
    suspend fun markAsSynced(id: Int)

    @Query("UPDATE job_logs SET isSynced = 1, syncId = :serverId WHERE syncId = :localId")
    suspend fun markAsSyncedWithId(localId: String, serverId: String)

    @Delete
    suspend fun deleteJobLog(jobLog: JobLogEntity)

    @Query("DELETE FROM job_logs WHERE id = :id")
    suspend fun deleteJobLogById(id: Int)

    @Query("DELETE FROM job_logs")
    suspend fun deleteAllJobLogs()

    @Query("SELECT COUNT(*) FROM job_logs WHERE isSynced = 0")
    fun getUnsyncedCount(): Flow<Int>

    @Query("SELECT COUNT(*) FROM job_logs WHERE status = 'completed' AND completedAt >= :startTime")
    fun getCompletedCountSince(startTime: Long): Flow<Int>
}
