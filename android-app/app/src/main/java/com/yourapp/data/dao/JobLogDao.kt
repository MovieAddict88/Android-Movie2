package com.yourapp.data.dao

import androidx.room.*
import com.yourapp.data.entity.JobLog

@Dao
interface JobLogDao {
    @Query("SELECT * FROM job_logs WHERE isSynced = 0")
    suspend fun getUnsyncedLogs(): List<JobLog>

    @Insert
    suspend fun insertLog(log: JobLog): Long

    @Update
    suspend fun updateLog(log: JobLog)

    @Query("UPDATE job_logs SET isSynced = 1 WHERE id = :id")
    suspend fun markAsSynced(id: Long)
}
