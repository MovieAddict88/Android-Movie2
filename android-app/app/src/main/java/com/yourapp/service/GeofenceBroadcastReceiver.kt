package com.yourapp.service

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.util.Log
import com.google.android.gms.location.Geofence
import com.google.android.gms.location.GeofencingEvent
import com.yourapp.data.database.AppDatabase
import com.yourapp.data.entity.JobLog
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

class GeofenceBroadcastReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        val event = GeofencingEvent.fromIntent(intent) ?: return
        if (event.hasError()) return

        val transition = event.geofenceTransition
        val triggeringGeofences = event.triggeringGeofences ?: return

        val pendingResult = goAsync()
        val database = AppDatabase.getDatabase(context)
        val jobLogDao = database.jobLogDao()

        CoroutineScope(Dispatchers.IO).launch {
            try {
                for (geofence in triggeringGeofences) {
                    Log.d("Geofence", "Transition $transition for ${geofence.requestId}")
                    val locationId = geofence.requestId.toLongOrNull() ?: continue

                    if (transition == Geofence.GEOFENCE_TRANSITION_ENTER) {
                        jobLogDao.insertLog(JobLog(
                            locationId = locationId,
                            checkInAt = System.currentTimeMillis()
                        ))
                    }
                }
            } finally {
                pendingResult.finish()
            }
        }
    }
}
