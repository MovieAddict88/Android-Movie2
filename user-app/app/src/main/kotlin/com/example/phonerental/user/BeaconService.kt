package com.example.phonerental.user

import android.app.*
import android.app.admin.DevicePolicyManager
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.os.IBinder
import android.provider.Settings
import android.util.Log
import androidx.core.app.NotificationCompat
import kotlinx.coroutines.*
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.GET
import retrofit2.http.Query

interface ApiService {
    @GET("api/status.php")
    suspend fun getStatus(@Query("device_id") deviceId: String): StatusResponse

    @GET("api/command.php")
    suspend fun getCommands(@Query("device_id") deviceId: String): CommandResponse

    @POST("api/register.php")
    suspend fun register(@retrofit2.http.Body body: RegisterRequest): GenericResponse
}

data class StatusResponse(val status: String, val message: String?, val rental_end: String?, val is_locked: Boolean)
data class RegisterRequest(val device_id: String, val model: String)
data class GenericResponse(val status: String, val message: String)
data class CommandResponse(val status: String, val commands: List<Command>)
data class Command(val id: Int, val command: String, val payload: String?)

class ApiKeyInterceptor(private val apiKey: String) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request().newBuilder()
            .addHeader("X-API-Key", apiKey)
            .build()
        return chain.proceed(request)
    }
}

class BeaconService : Service() {
    private val serviceJob = Job()
    private val serviceScope = CoroutineScope(Dispatchers.IO + serviceJob)
    private lateinit var apiService: ApiService
    private lateinit var deviceId: String
    private lateinit var devicePolicyManager: DevicePolicyManager
    private lateinit var adminComponent: ComponentName

    companion object {
        const val ACTION_REFRESH_UI = "com.example.phonerental.user.REFRESH_UI"
    }

    override fun onCreate() {
        super.onCreate()
        deviceId = Settings.Secure.getString(contentResolver, Settings.Secure.ANDROID_ID)
        devicePolicyManager = getSystemService(Context.DEVICE_POLICY_SERVICE) as DevicePolicyManager
        adminComponent = ComponentName(this, DeviceAdminReceiver::class.java)

        val client = OkHttpClient.Builder()
            .addInterceptor(ApiKeyInterceptor("your_secure_api_key_here_123")) // Same key as PHP
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl("http://your-admin-panel-url.com/") // Replace with actual URL
            .client(client)
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
            var delayMs = 60000L
            while (isActive) {
                try {
                    val status = apiService.getStatus(deviceId)

                    if (status.status == "error" && status.message?.contains("not found", ignoreCase = true) == true) {
                        Log.d("BeaconService", "Device not found, registering...")
                        val registerResponse = apiService.register(RegisterRequest(deviceId, android.os.Build.MODEL))
                        Log.d("BeaconService", "Registration result: ${registerResponse.status}")
                        // Wait a bit before next attempt to let server process
                        delay(5000)
                        continue
                    }

                    handleStatus(status)

                    val cmdResponse = apiService.getCommands(deviceId)
                    cmdResponse.commands.forEach { handleCommand(it) }

                    delayMs = 60000L // Reset delay on success
                } catch (e: Exception) {
                    Log.e("BeaconService", "Error polling: ${e.message}")
                    delayMs = (delayMs * 1.5).toLong().coerceAtMost(300000L) // Exponential backoff up to 5 min
                }
                delay(delayMs)
            }
        }
    }

    private fun handleStatus(status: StatusResponse) {
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        val wasLocked = prefs.getBoolean("is_locked", false)

        prefs.edit().apply {
            putString("rental_end", status.rental_end)
            putBoolean("is_locked", status.is_locked)
            apply()
        }

        if (wasLocked != status.is_locked) {
            sendBroadcast(Intent(ACTION_REFRESH_UI))
        }
    }

    private fun handleCommand(command: Command) {
        when (command.command) {
            "lock" -> {
                updateLockState(true)
            }
            "unlock" -> {
                updateLockState(false)
            }
            "hide_app" -> {
                command.payload?.let { setAppHidden(it, true) }
            }
            "show_app" -> {
                command.payload?.let { setAppHidden(it, false) }
            }
        }
    }

    private fun updateLockState(locked: Boolean) {
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        prefs.edit().putBoolean("is_locked", locked).apply()

        if (locked) {
            val intent = Intent(this, MainActivity::class.java)
            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
            intent.putExtra("ACTION", "LOCK")
            startActivity(intent)
        } else {
            sendBroadcast(Intent(ACTION_REFRESH_UI))
        }
    }

    private fun setAppHidden(packageName: String, hidden: Boolean) {
        try {
            if (devicePolicyManager.isDeviceOwnerApp(packageName)) return

            val success = devicePolicyManager.setApplicationHidden(adminComponent, packageName, hidden)
            Log.d("BeaconService", "Setting $packageName hidden=$hidden success=$success")
        } catch (e: Exception) {
            Log.e("BeaconService", "Failed to set app hidden: ${e.message}")
        }
    }

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onDestroy() {
        super.onDestroy()
        serviceJob.cancel()
    }
}
