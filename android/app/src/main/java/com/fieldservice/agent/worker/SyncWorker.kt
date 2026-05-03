package com.fieldservice.agent.worker

import android.content.Context
import android.util.Log
import androidx.hilt.work.HiltWorker
import androidx.work.*
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.JobLogUploadRequest
import com.fieldservice.agent.data.remote.api.SyncResponse
import com.fieldservice.agent.data.repository.JobLogRepository
import com.fieldservice.agent.data.repository.LocationRepository
import com.fieldservice.agent.util.NetworkResult
import dagger.assisted.Assisted
import dagger.assisted.AssistedInject
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.text.SimpleDateFormat
import java.util.*
import java.util.concurrent.TimeUnit

@HiltWorker
class SyncWorker @AssistedInject constructor(
    @Assisted context: Context,
    @Assisted workerParams: WorkerParameters,
    private val locationRepository: LocationRepository,
    private val jobLogRepository: JobLogRepository,
    private val apiService: ApiService
) : CoroutineWorker(context, workerParams) {

    override suspend fun doWork(): Result = withContext(Dispatchers.IO) {
        val startTime = System.currentTimeMillis()
        Log.d(TAG, "Starting sync work - attempt: ${runAttemptCount}")

        try {
            setForeground(createForegroundInfo())

            val syncResult = performSync()
            val duration = System.currentTimeMillis() - startTime

            if (syncResult) {
                Log.d(TAG, "Sync completed successfully in ${duration}ms")
                
                // Show completion notification
                showCompletionNotification(success = true, syncedCount = getPendingSyncCount())
                
                Result.success()
            } else {
                Log.w(TAG, "Sync completed with some failures in ${duration}ms")
                
                if (runAttemptCount < MAX_RETRY_ATTEMPTS) {
                    showCompletionNotification(success = false, syncedCount = 0)
                    Result.retry()
                } else {
                    showCompletionNotification(success = false, syncedCount = 0)
                    Result.failure()
                }
            }
        } catch (e: Exception) {
            val duration = System.currentTimeMillis() - startTime
            Log.e(TAG, "Sync failed after ${duration}ms", e)
            
            if (runAttemptCount < MAX_RETRY_ATTEMPTS) {
                Result.retry()
            } else {
                showCompletionNotification(success = false, syncedCount = 0)
                Result.failure()
            }
        }
    }

    private suspend fun performSync(): Boolean {
        var allSuccess = true

        // 1. Sync locations from server
        Log.d(TAG, "Syncing locations from server...")
        val syncLocationsResult = when (val result = locationRepository.syncLocations()) {
            is NetworkResult.Success -> {
                Log.d(TAG, "Synced ${result.data?.size ?: 0} locations")
                true
            }
            is NetworkResult.Error -> {
                Log.e(TAG, "Failed to sync locations: ${result.message}")
                false
            }
            else -> false
        }

        if (!syncLocationsResult) {
            allSuccess = false
        }

        // 2. Upload unsynced job logs
        Log.d(TAG, "Uploading unsynced job logs...")
        val unsyncedLogs = jobLogRepository.getUnsyncedJobLogs()
        Log.d(TAG, "Found ${unsyncedLogs.size} unsynced job logs")

        for (jobLog in unsyncedLogs) {
            try {
                val success = uploadJobLog(jobLog)
                if (success) {
                    jobLogRepository.markAsSynced(jobLog.id)
                    Log.d(TAG, "Successfully synced job log: ${jobLog.syncId}")
                } else {
                    Log.w(TAG, "Failed to upload job log: ${jobLog.syncId}")
                    allSuccess = false
                }
            } catch (e: Exception) {
                Log.e(TAG, "Error uploading job log ${jobLog.syncId}", e)
                allSuccess = false
            }
        }

        // 3. Fetch latest data from server
        Log.d(TAG, "Fetching latest data from server...")
        try {
            val lastSyncAt = getLastSyncTime()
            val serverData = apiService.sync(lastSyncAt)
            if (serverData.success) {
                processServerSyncData(serverData)
                saveLastSyncTime()
            }
        } catch (e: Exception) {
            Log.e(TAG, "Failed to fetch server sync data", e)
            // Don't fail the whole sync for this
        }

        return allSuccess || unsyncedLogs.isEmpty()
    }

    private suspend fun uploadJobLog(jobLog: JobLogEntity): Boolean {
        val dateFormat = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'", Locale.US).apply {
            timeZone = TimeZone.getTimeZone("UTC")
        }

        val request = JobLogUploadRequest(
            locationId = jobLog.locationId,
            syncId = jobLog.syncId,
            status = jobLog.status,
            startedAt = dateFormat.format(Date(jobLog.startedAt)),
            completedAt = jobLog.completedAt?.let { dateFormat.format(Date(it)) } ?: "",
            latitudeStart = jobLog.latitudeStart,
            longitudeStart = jobLog.longitudeStart,
            latitudeEnd = jobLog.latitudeEnd,
            longitudeEnd = jobLog.longitudeEnd,
            notes = jobLog.notes,
            photoBase64 = jobLog.photoBase64,
            metadata = null
        )

        val response = apiService.uploadJobLog(request)
        return response.isSuccessful && response.body()?.success == true
    }

    private fun processServerSyncData(response: SyncResponse) {
        // Update locations from server
        response.locations?.let { locations ->
            Log.d(TAG, "Processing ${locations.size} locations from server")
            // Location data is already synced via syncLocations()
        }

        // Process job logs from server
        response.jobLogs?.let { jobLogs ->
            Log.d(TAG, "Processing ${jobLogs.size} job logs from server")
            // Could update local job logs from server data if needed
        }
    }

    private fun getLastSyncTime(): String? {
        val prefs = applicationContext.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        return prefs.getString(KEY_LAST_SYNC, null)
    }

    private fun saveLastSyncTime() {
        val dateFormat = SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'", Locale.US).apply {
            timeZone = TimeZone.getTimeZone("UTC")
        }
        
        applicationContext.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
            .edit()
            .putString(KEY_LAST_SYNC, dateFormat.format(Date()))
            .apply()
    }

    private fun getPendingSyncCount(): Int {
        return runCatching {
            kotlinx.coroutines.runBlocking {
                jobLogRepository.getUnsyncedJobLogs().size
            }
        }.getOrDefault(0)
    }

    private fun createForegroundInfo(): ForegroundInfo {
        val notification = androidx.core.app.NotificationCompat.Builder(
            applicationContext,
            com.fieldservice.agent.FieldServiceApp.CHANNEL_SYNC
        )
            .setSmallIcon(android.R.drawable.ic_popup_sync)
            .setContentTitle("Syncing Data")
            .setContentText("Uploading job data to server...")
            .setProgress(0, 0, true)
            .setOngoing(true)
            .setPriority(androidx.core.app.NotificationCompat.PRIORITY_LOW)
            .build()

        return ForegroundInfo(FOREGROUND_NOTIFICATION_ID, notification)
    }

    private fun showCompletionNotification(success: Boolean, syncedCount: Int) {
        val notification = androidx.core.app.NotificationCompat.Builder(
            applicationContext,
            com.fieldservice.agent.FieldServiceApp.CHANNEL_SYNC
        )
            .setSmallIcon(
                if (success) android.R.drawable.ic_dialog_info 
                else android.R.drawable.ic_dialog_alert
            )
            .setContentTitle(if (success) "Sync Complete" else "Sync Failed")
            .setContentText(
                if (success) "Successfully synced $syncedCount job(s)"
                else "Some data failed to sync. Will retry automatically."
            )
            .setPriority(androidx.core.app.NotificationCompat.PRIORITY_DEFAULT)
            .setAutoCancel(true)
            .build()

        val notificationManager = androidx.core.app.NotificationManagerCompat.from(applicationContext)
        try {
            notificationManager.notify(COMPLETION_NOTIFICATION_ID, notification)
        } catch (e: SecurityException) {
            Log.e(TAG, "Cannot show notification - permission denied")
        }
    }

    companion object {
        private const val TAG = "SyncWorker"
        private const val WORK_NAME = "field_service_sync"
        private const val PERIODIC_WORK_NAME = "field_service_sync_periodic"
        private const val FOREGROUND_NOTIFICATION_ID = 100
        private const val COMPLETION_NOTIFICATION_ID = 101
        private const val MAX_RETRY_ATTEMPTS = 3
        private const val PREFS_NAME = "sync_prefs"
        private const val KEY_LAST_SYNC = "last_sync_time"

        fun enqueueSyncWork(context: Context, unique: Boolean = false) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .setRequiresBatteryNotLow(true)
                .build()

            val workRequest = OneTimeWorkRequestBuilder<SyncWorker>()
                .setConstraints(constraints)
                .setBackoffCriteria(
                    BackoffPolicy.EXPONENTIAL,
                    WorkRequest.MIN_BACKOFF_MILLIS,
                    TimeUnit.MILLISECONDS
                )
                .build()

            val workManager = WorkManager.getInstance(context)

            if (unique) {
                workManager.enqueueUniqueWork(
                    WORK_NAME,
                    ExistingWorkPolicy.REPLACE,
                    workRequest
                )
            } else {
                workManager.enqueue(workRequest)
            }

            Log.d(TAG, "Sync work enqueued (unique: $unique)")
        }

        fun enqueuePeriodicSync(context: Context) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
                .build()

            val periodicWorkRequest = PeriodicWorkRequestBuilder<SyncWorker>(
                15, TimeUnit.MINUTES,
                5, TimeUnit.MINUTES
            )
                .setConstraints(constraints)
                .build()

            WorkManager.getInstance(context).enqueueUniquePeriodicWork(
                PERIODIC_WORK_NAME,
                ExistingPeriodicWorkPolicy.KEEP,
                periodicWorkRequest
            )

            Log.d(TAG, "Periodic sync work enqueued")
        }

        fun cancelSyncWork(context: Context) {
            WorkManager.getInstance(context).cancelUniqueWork(WORK_NAME)
            Log.d(TAG, "Sync work cancelled")
        }

        fun cancelAllSyncWork(context: Context) {
            WorkManager.getInstance(context).cancelAllWorkByTag(WORK_NAME)
            Log.d(TAG, "All sync work cancelled")
        }
    }
}