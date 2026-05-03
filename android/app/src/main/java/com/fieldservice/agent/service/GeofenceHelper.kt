package com.fieldservice.agent.service

import android.Manifest
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.util.Log
import androidx.core.app.ActivityCompat
import com.google.android.gms.location.Geofence
import com.google.android.gms.location.GeofencingClient
import com.google.android.gms.location.GeofencingRequest
import com.google.android.gms.location.LocationServices
import com.fieldservice.agent.data.entity.LocationEntity

class GeofenceHelper(private val context: Context) {

    private val geofencingClient: GeofencingClient =
        LocationServices.getGeofencingClient(context)

    private val geofencePendingIntent: PendingIntent by lazy {
        val intent = Intent(context, GeofenceBroadcastReceiver::class.java).apply {
            action = ACTION_GEOFENCE_EVENT
        }
        PendingIntent.getBroadcast(
            context,
            GEOFENCE_PENDING_REQUEST_CODE,
            intent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_MUTABLE
        )
    }

    fun addGeofences(locations: List<LocationEntity>, onSuccess: () -> Unit, onFailure: (Exception) -> Unit) {
        if (!hasLocationPermission()) {
            Log.e(TAG, "Location permission not granted")
            onFailure(SecurityException("Location permission not granted"))
            return
        }

        val geofences = locations.map { location ->
            Geofence.Builder()
                .setRequestId(location.id.toString())
                .setCircularRegion(
                    location.latitude,
                    location.longitude,
                    location.radius.toFloat()
                )
                .setExpirationDuration(Geofence.NEVER_EXPIRE)
                .setTransitionTypes(
                    Geofence.GEOFENCE_TRANSITION_ENTER or Geofence.GEOFENCE_TRANSITION_EXIT
                )
                .setNotificationResponsiveness(GEOFENCE_NOTIFICATION_RESPONSIVENESS)
                .build()
        }

        if (geofences.isEmpty()) {
            onSuccess()
            return
        }

        val geofencingRequest = GeofencingRequest.Builder()
            .setInitialTrigger(GeofencingRequest.INITIAL_TRIGGER_ENTER)
            .addGeofences(geofences)
            .build()

        try {
            if (ActivityCompat.checkSelfPermission(
                    context,
                    Manifest.permission.ACCESS_FINE_LOCATION
                ) == PackageManager.PERMISSION_GRANTED
            ) {
                geofencingClient.addGeofences(geofencingRequest, geofencePendingIntent)
                    .addOnSuccessListener {
                        Log.d(TAG, "Added ${geofences.size} geofences")
                        onSuccess()
                    }
                    .addOnFailureListener { e ->
                        Log.e(TAG, "Failed to add geofences", e)
                        onFailure(e)
                    }
            }
        } catch (e: SecurityException) {
            Log.e(TAG, "Security exception adding geofences", e)
            onFailure(e)
        }
    }

    fun removeGeofence(locationId: Int) {
        geofencingClient.removeGeofences(listOf(locationId.toString()))
            .addOnSuccessListener {
                Log.d(TAG, "Removed geofence for location $locationId")
            }
            .addOnFailureListener { e ->
                Log.e(TAG, "Failed to remove geofence for location $locationId", e)
            }
    }

    fun removeAllGeofences() {
        geofencingClient.removeGeofences(geofencePendingIntent)
            .addOnSuccessListener {
                Log.d(TAG, "Removed all geofences")
            }
            .addOnFailureListener { e ->
                Log.e(TAG, "Failed to remove all geofences", e)
            }
    }

    fun hasLocationPermission(): Boolean {
        return ActivityCompat.checkSelfPermission(
            context,
            Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED
    }

    fun hasBackgroundLocationPermission(): Boolean {
        return if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.Q) {
            ActivityCompat.checkSelfPermission(
                context,
                Manifest.permission.ACCESS_BACKGROUND_LOCATION
            ) == PackageManager.PERMISSION_GRANTED
        } else {
            hasLocationPermission()
        }
    }

    companion object {
        private const val TAG = "GeofenceHelper"
        const val ACTION_GEOFENCE_EVENT = "com.fieldservice.agent.ACTION_GEOFENCE_EVENT"
        private const val GEOFENCE_PENDING_REQUEST_CODE = 1001
        private const val GEOFENCE_NOTIFICATION_RESPONSIVENESS = 5000
    }
}
