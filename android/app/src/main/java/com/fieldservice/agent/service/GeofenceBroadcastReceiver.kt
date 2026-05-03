package com.fieldservice.agent.service

import android.app.NotificationManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.util.Log
import androidx.core.app.NotificationCompat
import com.google.android.gms.location.Geofence
import com.google.android.gms.location.GeofenceStatusCodes
import com.google.android.gms.location.GeofencingEvent
import com.fieldservice.agent.FieldServiceApp
import com.fieldservice.agent.R

class GeofenceBroadcastReceiver : BroadcastReceiver() {

    override fun onReceive(context: Context, intent: Intent) {
        val geofencingEvent = GeofencingEvent.fromIntent(intent) ?: return

        if (geofencingEvent.hasError()) {
            val errorMessage = GeofenceStatusCodes.getStatusCodeString(geofencingEvent.errorCode)
            Log.e(TAG, "Geofence error: $errorMessage")
            return
        }

        val geofenceTransition = geofencingEvent.geofenceTransition
        val triggeringGeofences = geofencingEvent.triggeringGeofences ?: return

        when (geofenceTransition) {
            Geofence.GEOFENCE_TRANSITION_ENTER -> {
                handleEnterEvent(context, triggeringGeofences)
            }
            Geofence.GEOFENCE_TRANSITION_EXIT -> {
                handleExitEvent(context, triggeringGeofences)
            }
            else -> {
                Log.w(TAG, "Unknown geofence transition type: $geofenceTransition")
            }
        }
    }

    private fun handleEnterEvent(context: Context, geofences: List<Geofence>) {
        for (geofence in geofences) {
            val locationId = geofence.requestId.toIntOrNull() ?: continue

            Log.d(TAG, "Entered geofence for location ID: $locationId")

            val notification = NotificationCompat.Builder(context, FieldServiceApp.CHANNEL_GEOFENCE)
                .setSmallIcon(R.drawable.ic_location)
                .setContentTitle("Entered Job Site")
                .setContentText("You have arrived at the job location")
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setAutoCancel(true)
                .setCategory(NotificationCompat.CATEGORY_MESSAGE)
                .build()

            val notificationManager = context.getSystemService(NotificationManager::class.java)
            notificationManager.notify(NOTIFICATION_ID_ENTER + locationId, notification)

            GeofenceEventManager.onEnteredGeofence(locationId)
        }
    }

    private fun handleExitEvent(context: Context, geofences: List<Geofence>) {
        for (geofence in geofences) {
            val locationId = geofence.requestId.toIntOrNull() ?: continue

            Log.d(TAG, "Exited geofence for location ID: $locationId")

            val notification = NotificationCompat.Builder(context, FieldServiceApp.CHANNEL_GEOFENCE)
                .setSmallIcon(R.drawable.ic_location)
                .setContentTitle("Left Job Site")
                .setContentText("You have left the job location area")
                .setPriority(NotificationCompat.PRIORITY_HIGH)
                .setAutoCancel(true)
                .setCategory(NotificationCompat.CATEGORY_MESSAGE)
                .build()

            val notificationManager = context.getSystemService(NotificationManager::class.java)
            notificationManager.notify(NOTIFICATION_ID_EXIT + locationId, notification)

            GeofenceEventManager.onExitedGeofence(locationId)
        }
    }

    companion object {
        private const val TAG = "GeofenceBroadcastReceiver"
        private const val NOTIFICATION_ID_ENTER = 2000
        private const val NOTIFICATION_ID_EXIT = 3000
    }
}

object GeofenceEventManager {
    private val listeners = mutableListOf<GeofenceListener>()

    fun addListener(listener: GeofenceListener) {
        listeners.add(listener)
    }

    fun removeListener(listener: GeofenceListener) {
        listeners.remove(listener)
    }

    private fun onEnteredGeofence(locationId: Int) {
        listeners.forEach { it.onGeofenceEntered(locationId) }
    }

    private fun onExitedGeofence(locationId: Int) {
        listeners.forEach { it.onGeofenceExited(locationId) }
    }
}

interface GeofenceListener {
    fun onGeofenceEntered(locationId: Int)
    fun onGeofenceExited(locationId: Int)
}
