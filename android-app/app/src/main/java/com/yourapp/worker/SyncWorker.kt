package com.yourapp.worker

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.yourapp.data.database.AppDatabase
import com.yourapp.data.api.RetrofitClient
import com.yourapp.data.api.JobLogRequest
import android.util.Log
import java.time.Instant
import java.time.ZoneId
import java.time.format.DateTimeFormatter

class SyncWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {
    private val formatter = DateTimeFormatter.ofPattern("yyyy-MM-dd HH:mm:ss")
        .withZone(ZoneId.systemDefault())

    override suspend fun doWork(): Result {
        val database = AppDatabase.getDatabase(applicationContext)
        val jobLogDao = database.jobLogDao()
        val apiService = RetrofitClient.apiService

        return try {
            val unsyncedLogs = jobLogDao.getUnsyncedLogs()
            if (unsyncedLogs.isNotEmpty()) {
                Log.d("SyncWorker", "Syncing ${unsyncedLogs.size} logs to server...")

                for (log in unsyncedLogs) {
                    val request = JobLogRequest(
                        location_id = log.locationId,
                        check_in_at = formatter.format(Instant.ofEpochMilli(log.checkInAt)),
                        check_out_at = log.checkOutAt?.let { formatter.format(Instant.ofEpochMilli(it)) } ?: "",
                        notes = log.notes,
                        photo_base64 = null
                    )

                    val response = apiService.uploadJobLog(request)
                    if (response.isSuccessful) {
                        jobLogDao.markAsSynced(log.id)
                        Log.d("SyncWorker", "Log ${log.id} synced successfully")
                    } else {
                        Log.e("SyncWorker", "Failed to sync log ${log.id}: ${response.errorBody()?.string()}")
                        return Result.retry()
                    }
                }
                Log.d("SyncWorker", "Sync completed successfully")
            }
            Result.success()
        } catch (e: Exception) {
            Log.e("SyncWorker", "Sync failed", e)
            Result.retry()
        }
    }
}
