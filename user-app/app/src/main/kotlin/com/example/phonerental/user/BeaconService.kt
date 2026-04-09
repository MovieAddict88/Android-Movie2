package com.example.phonerental.user

import android.app.*
import android.content.Context
import android.content.Intent
import android.os.IBinder
import android.provider.Settings
import android.util.Log
import androidx.core.app.NotificationCompat
import kotlinx.coroutines.*
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.GET
import retrofit2.http.Query

interface ApiService {
    @GET("api/status.php")
    suspend fun getStatus(@Query("device_id") deviceId: String): StatusResponse

    @GET("api/command.php")
    suspend fun getCommands(@Query("device_id") deviceId: String): CommandResponse
}

data class StatusResponse(val rental_end: String?, val is_locked: Boolean)
data class CommandResponse(val commands: List<Command>)
data class Command(val id: Int, val command: String, val payload: String?)

class BeaconService : Service() {
    private val serviceJob = Job()
    private val serviceScope = CoroutineScope(Dispatchers.IO + serviceJob)
    private lateinit var apiService: ApiService
    private lateinit var deviceId: String

    override fun onCreate() {
        super.onCreate()
        deviceId = Settings.Secure.getString(contentResolver, Settings.Secure.ANDROID_ID)

        val retrofit = Retrofit.Builder()
            .baseUrl("http://your-admin-panel-url.com/") // Replace with actual URL
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        apiService = retrofit.create(ApiService::class.java)

        startForegroundService()
        startPolling()
    }

    private fun startForegroundService() {
        val channelId = "rental_service"
        val channelName = "Rental Management Service"
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            val chan = NotificationChannel(channelId, channelName, NotificationManager.IMPORTANCE_LOW)
            val manager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            manager.createNotificationChannel(chan)
        }

        val notification = NotificationCompat.Builder(this, channelId)
            .setContentTitle("Device Managed")
            .setContentText("This device is under rental management.")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .build()
        startForeground(1, notification)
    }

    private fun startPolling() {
        serviceScope.launch {
            while (isActive) {
                try {
                    val status = apiService.getStatus(deviceId)
                    handleStatus(status)

                    val cmdResponse = apiService.getCommands(deviceId)
                    cmdResponse.commands.forEach { handleCommand(it) }
                } catch (e: Exception) {
                    Log.e("BeaconService", "Error polling: ${e.message}")
                }
                delay(60000) // Poll every 1 minute
            }
        }
    }

    private fun handleStatus(status: StatusResponse) {
        // Update local preferences or notify MainActivity
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        prefs.edit().apply {
            putString("rental_end", status.rental_end)
            putBoolean("is_locked", status.is_locked)
            apply()
        }
    }

    private fun handleCommand(command: Command) {
        when (command.command) {
            "lock" -> {
                val intent = Intent(this, MainActivity::class.java)
                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
                intent.putExtra("ACTION", "LOCK")
                startActivity(intent)
            }
            "unlock" -> {
                 // logic to unlock
            }
        }
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        super.onDestroy()
        serviceJob.cancel()
    }
}
