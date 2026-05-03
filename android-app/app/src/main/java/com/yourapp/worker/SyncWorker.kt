package com.yourapp.worker

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.yourapp.data.database.AppDatabase
import android.util.Log

class SyncWorker(context: Context, params: WorkerParameters) : CoroutineWorker(context, params) {
    override suspend fun doWork(): Result {
        val database = AppDatabase.getDatabase(applicationContext)
        val jobLogDao = database.jobLogDao()

        return try {
            val unsyncedLogs = jobLogDao.getUnsyncedLogs()
            if (unsyncedLogs.isNotEmpty()) {
                Log.d("SyncWorker", "Syncing ${unsyncedLogs.size} logs to server...")

                // Mocking a successful API call
                val isSuccess = true

                if (isSuccess) {
                    unsyncedLogs.forEach { log ->
                        jobLogDao.markAsSynced(log.id)
                    }
                    Log.d("SyncWorker", "Sync completed successfully")
                } else {
                    return Result.retry()
                }
            }
            Result.success()
        } catch (e: Exception) {
            Log.e("SyncWorker", "Sync failed", e)
            Result.retry()
        }
    }
}
