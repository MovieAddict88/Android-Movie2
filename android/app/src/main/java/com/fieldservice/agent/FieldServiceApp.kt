package com.fieldservice.agent

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build
import androidx.hilt.work.HiltWorkerFactory
import androidx.work.Configuration
import dagger.hilt.android.HiltAndroidApp
import javax.inject.Inject

@HiltAndroidApp
class FieldServiceApp : Application(), Configuration.Provider {

    @Inject
    lateinit var workerFactory: HiltWorkerFactory

    override val workManagerConfiguration: Configuration
        get() = Configuration.Builder()
            .setWorkerFactory(workerFactory)
            .build()

    override fun onCreate() {
        super.onCreate()
        createNotificationChannels()
    }

    private fun createNotificationChannels() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val notificationManager = getSystemService(NotificationManager::class.java)

            val jobChannel = NotificationChannel(
                CHANNEL_JOBS,
                "Job Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Notifications for new job assignments"
                enableVibration(true)
            }

            val geofenceChannel = NotificationChannel(
                CHANNEL_GEOFENCE,
                "Geofence Alerts",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "Alerts when entering or leaving job sites"
                enableVibration(true)
            }

            val syncChannel = NotificationChannel(
                CHANNEL_SYNC,
                "Sync Status",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Background sync status updates"
            }

            notificationManager.createNotificationChannels(
                listOf(jobChannel, geofenceChannel, syncChannel)
            )
        }
    }

    companion object {
        const val CHANNEL_JOBS = "field_service_jobs"
        const val CHANNEL_GEOFENCE = "field_service_geofence"
        const val CHANNEL_SYNC = "field_service_sync"
    }
}
