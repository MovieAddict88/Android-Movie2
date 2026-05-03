package com.yourapp.service

import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import android.util.Log

class FirebaseMessagingService : FirebaseMessagingService() {
    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
        val data = message.data
        if (data.containsKey("type") && data["type"] == "NEW_JOB") {
            // Handle new job notification
            Log.d("FCM", "New job received: ${data["name"]}")
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        // Send token to server
    }
}
