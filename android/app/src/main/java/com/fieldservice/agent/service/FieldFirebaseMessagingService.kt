package com.fieldservice.agent.service

import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.util.Log
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.fieldservice.agent.FieldServiceApp
import com.fieldservice.agent.R
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.DeviceTokenRequest
import com.fieldservice.agent.data.repository.AuthRepository
import com.fieldservice.agent.ui.MainActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import kotlinx.coroutines.launch
import javax.inject.Inject

@AndroidEntryPoint
class FieldFirebaseMessagingService : FirebaseMessagingService() {

    @Inject
    lateinit var apiService: ApiService

    @Inject
    lateinit var authRepository: AuthRepository

    private val serviceScope = CoroutineScope(SupervisorJob() + Dispatchers.IO)

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d(TAG, "New FCM token received")

        saveToken(token)
        sendTokenToServer(token)
    }

    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
        Log.d(TAG, "FCM message received from: ${message.from}")

        message.data.let { data ->
            handleDataMessage(data)
        }

        message.notification?.let { notification ->
            showNotification(
                title = notification.title ?: getString(R.string.app_name),
                body = notification.body ?: "",
                data = message.data
            )
        }
    }

    private fun handleDataMessage(data: Map<String, String>) {
        val type = data["type"]

        when (type) {
            "job_assigned" -> {
                val locationId = data["location_id"]?.toIntOrNull()
                val locationName = data["location_name"]
                val address = data["address"]

                if (locationId != null) {
                    showNotification(
                        title = "New Job Assigned",
                        body = "You have a new job: $locationName\n$address",
                        data = data
                    )
                }
            }
            "location_updated" -> {
                val locationId = data["location_id"]?.toIntOrNull()
                showNotification(
                    title = "Job Updated",
                    body = "One of your job assignments has been updated",
                    data = data
                )
            }
            "sync_request" -> {
                SyncWorker.enqueueSyncWork(applicationContext)
            }
            else -> {
                Log.d(TAG, "Unknown message type: $type")
            }
        }
    }

    private fun showNotification(title: String, body: String, data: Map<String, String>) {
        val intent = Intent(this, MainActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP
            data.forEach { (key, value) ->
                putExtra("extra_$key", value)
            }
        }

        val pendingIntent = PendingIntent.getActivity(
            this,
            0,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )

        val notification = NotificationCompat.Builder(this, FieldServiceApp.CHANNEL_JOBS)
            .setSmallIcon(R.drawable.ic_notification)
            .setContentTitle(title)
            .setContentText(body)
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setCategory(NotificationCompat.CATEGORY_MESSAGE)
            .setContentIntent(pendingIntent)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))
            .build()

        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
        notificationManager.notify(System.currentTimeMillis().toInt(), notification)
    }

    private fun saveToken(token: String) {
        serviceScope.launch {
            try {
                authRepository.saveDeviceToken(token)
            } catch (e: Exception) {
                Log.e(TAG, "Failed to save device token", e)
            }
        }
    }

    private fun sendTokenToServer(token: String) {
        serviceScope.launch {
            try {
                val deviceName = "${android.os.Build.MANUFACTURER} ${android.os.Build.MODEL}"
                apiService.updateDeviceToken(
                    DeviceTokenRequest(
                        deviceToken = token,
                        deviceName = deviceName,
                        deviceType = "android"
                    )
                )
                Log.d(TAG, "Token sent to server")
            } catch (e: Exception) {
                Log.e(TAG, "Failed to send token to server", e)
            }
        }
    }

    companion object {
        private const val TAG = "FieldFirebaseMsgService"
    }
}
