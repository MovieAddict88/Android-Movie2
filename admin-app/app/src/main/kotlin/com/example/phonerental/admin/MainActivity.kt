package com.example.phonerental.admin

import android.graphics.Color
import android.os.Bundle
import android.text.InputType
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.phonerental.admin.databinding.ActivityMainBinding
import com.example.phonerental.admin.databinding.ItemDeviceBinding
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.*
import okhttp3.Interceptor
import okhttp3.OkHttpClient
import okhttp3.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.*

data class Device(
    val id: Int,
    val device_id: String,
    val model: String?,
    val owner_name: String?,
    val is_locked: Boolean,
    val last_seen: String?,
    val ip_address: String?,
    val rental_end_time: String?
)
data class GenericResponse(val status: String, val message: String)

class ApiKeyInterceptor(private val apiKey: String) : Interceptor {
    override fun intercept(chain: Interceptor.Chain): Response {
        val request = chain.request().newBuilder()
            .addHeader("X-API-Key", apiKey)
            .build()
        return chain.proceed(request)
    }
}

interface AdminApiService {
    @GET("api/devices_list.php")
    suspend fun getDevices(): List<Device>

    @POST("api/send_command.php")
    @FormUrlEncoded
    suspend fun sendCommand(
        @Field("device_id") deviceId: String,
        @Field("command") command: String,
        @Field("payload") payload: String? = null
    ): GenericResponse
}

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private lateinit var apiService: AdminApiService
    private val scope = CoroutineScope(Dispatchers.Main + Job())
    private var allDevices: List<Device> = emptyList()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val client = OkHttpClient.Builder()
            .addInterceptor(ApiKeyInterceptor("your_secure_api_key_here_123"))
            .build()

        val retrofit = Retrofit.Builder()
            .baseUrl("http://your-admin-panel-url.com/")
            .client(client)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        apiService = retrofit.create(AdminApiService::class.java)

        binding.rvDevices.layoutManager = LinearLayoutManager(this)
        binding.swipeRefresh.setOnRefreshListener {
            refreshDevices()
        }

        binding.searchView.setOnQueryTextListener(object : androidx.appcompat.widget.SearchView.OnQueryTextListener {
            override fun onQueryTextSubmit(query: String?): Boolean = false
            override fun onQueryTextChange(newText: String?): Boolean {
                filterDevices(newText)
                return true
            }
        })

        refreshDevices()
    }

    private fun refreshDevices() {
        binding.swipeRefresh.isRefreshing = true
        scope.launch {
            try {
                allDevices = withContext(Dispatchers.IO) { apiService.getDevices() }
                updateAdapter(allDevices)
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
            } finally {
                binding.swipeRefresh.isRefreshing = false
            }
        }
    }

    private fun filterDevices(query: String?) {
        if (query.isNullOrBlank()) {
            updateAdapter(allDevices)
        } else {
            val filtered = allDevices.filter {
                it.model?.contains(query, ignoreCase = true) == true ||
                        it.device_id.contains(query, ignoreCase = true) ||
                        it.owner_name?.contains(query, ignoreCase = true) == true
            }
            updateAdapter(filtered)
        }
    }

    private fun updateAdapter(devices: List<Device>) {
        binding.rvDevices.adapter = DeviceAdapter(devices) { device, command ->
            when (command) {
                "hide_app", "show_app" -> showAppCommandDialog(device.device_id)
                "message" -> showMessageDialog(device.device_id)
                else -> sendCommand(device.device_id, command)
            }
        }
    }

    private fun sendCommand(deviceId: String, command: String, payload: String? = null) {
        scope.launch {
            try {
                val response = withContext(Dispatchers.IO) { apiService.sendCommand(deviceId, command, payload) }
                Toast.makeText(this@MainActivity, response.message, Toast.LENGTH_SHORT).show()
                refreshDevices()
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Failed: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun showAppCommandDialog(deviceId: String) {
        val input = EditText(this)
        input.hint = "Package Name (e.g. com.example.app)"
        input.inputType = InputType.TYPE_CLASS_TEXT
        val padding = (16 * resources.displayMetrics.density).toInt()
        input.setPadding(padding, padding, padding, padding)

        MaterialAlertDialogBuilder(this)
            .setTitle("Application Control")
            .setMessage("Enter the package name to hide/show on the device.")
            .setView(input)
            .setPositiveButton("Hide") { _, _ ->
                val packageName = input.text.toString()
                if (packageName.isNotEmpty()) sendCommand(deviceId, "hide_app", packageName)
            }
            .setNegativeButton("Show") { _, _ ->
                val packageName = input.text.toString()
                if (packageName.isNotEmpty()) sendCommand(deviceId, "show_app", packageName)
            }
            .setNeutralButton("Cancel", null)
            .show()
    }

    private fun showMessageDialog(deviceId: String) {
        val input = EditText(this)
        input.hint = "Enter message..."
        input.inputType = InputType.TYPE_CLASS_TEXT
        val padding = (16 * resources.displayMetrics.density).toInt()
        input.setPadding(padding, padding, padding, padding)

        MaterialAlertDialogBuilder(this)
            .setTitle("Send Message")
            .setMessage("The message will be displayed as a dialog on the user's phone.")
            .setView(input)
            .setPositiveButton("Send") { _, _ ->
                val message = input.text.toString()
                if (message.isNotEmpty()) sendCommand(deviceId, "message", message)
            }
            .setNegativeButton("Cancel", null)
            .show()
    }

    inner class DeviceAdapter(private val devices: List<Device>, private val onAction: (Device, String) -> Unit) :
        RecyclerView.Adapter<DeviceAdapter.ViewHolder>() {

        inner class ViewHolder(val binding: ItemDeviceBinding) : RecyclerView.ViewHolder(binding.root)

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
            val b = ItemDeviceBinding.inflate(LayoutInflater.from(parent.context), parent, false)
            return ViewHolder(b)
        }

        override fun onBindViewHolder(holder: ViewHolder, position: Int) {
            val device = devices[position]
            holder.binding.tvDeviceName.text = device.model ?: device.device_id
            holder.binding.tvOwner.text = "Owner: ${device.owner_name ?: "Unknown"}"
            holder.binding.tvLastSeen.text = "Last seen: ${device.last_seen ?: "Never"}"
            holder.binding.tvIpAddress.text = "IP: ${device.ip_address ?: "Unknown"}"
            holder.binding.tvRentalEnd.text = "Ends: ${device.rental_end_time ?: "Not set"}"

            // Simple online check (within 5 minutes)
            val isOnline = try {
                val sdf = java.text.SimpleDateFormat("yyyy-MM-dd HH:mm:ss", java.util.Locale.getDefault())
                val lastSeen = device.last_seen?.let { sdf.parse(it) }
                if (lastSeen != null) {
                    (System.currentTimeMillis() - lastSeen.time) < 5 * 60 * 1000
                } else false
            } catch (e: Exception) { false }

            if (isOnline) {
                holder.binding.ivStatusDot.setImageResource(android.R.drawable.presence_online)
            } else {
                holder.binding.ivStatusDot.setImageResource(android.R.drawable.presence_invisible)
            }

            if (device.is_locked) {
                holder.binding.btnLock.isEnabled = false
                holder.binding.btnUnlock.isEnabled = true
            } else {
                holder.binding.btnLock.isEnabled = true
                holder.binding.btnUnlock.isEnabled = false
            }

            holder.binding.btnLock.setOnClickListener { onAction(device, "lock") }
            holder.binding.btnUnlock.setOnClickListener { onAction(device, "unlock") }
            holder.binding.btnMessage.setOnClickListener { onAction(device, "message") }
            holder.binding.btnApps.setOnClickListener { onAction(device, "hide_app") } // Action trigger
        }

        override fun getItemCount() = devices.size
    }
}
