package com.fieldservice.agent.worker

import android.content.Context
import android.util.Log
import androidx.hilt.work.HiltWorker
import androidx.work.*
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.JobLogUploadRequest
import com.fieldservice.agent.data.repository.JobLogRepository
import com.fieldservice.agent.data.repository.LocationRepository
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
        Log.d(TAG, "Starting sync work")

        try {
            setForeground(createForegroundInfo())

            val syncResult = performSync()

            if (syncResult) {
                Log.d(TAG, "Sync completed successfully")
                Result.success()
            } else {
                Log.w(TAG, "Sync completed with some failures")
                Result.retry()
            }
        } catch (e: Exception) {
            Log.e(TAG, "Sync failed", e)
            if (runAttemptCount < MAX_RETRY_ATTEMPTS) {
                Result.retry()
            } else {
                Result.failure()
            }
        }
    }

    private suspend fun performSync(): Boolean {
        var allSuccess = true

        val syncLocationsResult = try {
            locationRepository.syncLocations()
            true
        } catch (e: Exception) {
            Log.e(TAG, "Failed to sync locations", e)
            false
        }

        if (!syncLocationsResult) {
            allSuccess = false
        }

        val unsyncedLogs = jobLogRepository.getUnsyncedJobLogs()

        for (jobLog in unsyncedLogs) {
            try {
                val success = uploadJobLog(jobLog)
                if (success) {
                    jobLogRepository.markAsSynced(jobLog.id)
                } else {
                    allSuccess = false
                }
            } catch (e: Exception) {
                Log.e(TAG, "Failed to upload job log ${jobLog.syncId}", e)
                allSuccess = false
            }
        }

        return allSuccess
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

    private fun createForegroundInfo(): ForegroundInfo {
        val notification = androidx.core.app.NotificationCompat.Builder(
            applicationContext,
            com.fieldservice.agent.FieldServiceApp.CHANNEL_SYNC
        )
            .setSmallIcon(android.R.drawable.ic_popup_sync)
            .setContentTitle("Syncing")
            .setContentText("Uploading job data to server")
            .setProgress(0, 0, true)
            .setOngoing(true)
            .build()

        return ForegroundInfo(FOREGROUND_NOTIFICATION_ID, notification)
    }

    companion object {
        private const val TAG = "SyncWorker"
        private const val WORK_NAME = "field_service_sync"
        private const val FOREGROUND_NOTIFICATION_ID = 100
        private const val MAX_RETRY_ATTEMPTS = 3

        fun enqueueSyncWork(context: Context, unique: Boolean = false) {
            val constraints = Constraints.Builder()
                .setRequiredNetworkType(NetworkType.CONNECTED)
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

            Log.d(TAG, "Sync work enqueued")
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
                "${WORK_NAME}_periodic",
                ExistingPeriodicWorkPolicy.KEEP,
                periodicWorkRequest
            )

            Log.d(TAG, "Periodic sync work enqueued")
        }

        fun cancelSyncWork(context: Context) {
            WorkManager.getInstance(context).cancelUniqueWork(WORK_NAME)
            Log.d(TAG, "Sync work cancelled")
        }
    }
}
