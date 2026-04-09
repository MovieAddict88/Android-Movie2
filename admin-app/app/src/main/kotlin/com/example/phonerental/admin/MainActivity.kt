package com.example.phonerental.admin

import android.os.Bundle
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.phonerental.admin.databinding.ActivityMainBinding
import com.example.phonerental.admin.databinding.ItemDeviceBinding
import kotlinx.coroutines.*
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.*

data class Device(val id: Int, val device_id: String, val model: String?, val owner_name: String?, val is_locked: Boolean)
data class GenericResponse(val status: String, val message: String)

interface AdminApiService {
    @GET("api/devices_list.php") // Need to create this endpoint
    suspend fun getDevices(): List<Device>

    @POST("api/send_command.php") // Need to create this endpoint
    @FormUrlEncoded
    suspend fun sendCommand(@Field("device_id") deviceId: String, @Field("command") command: String): GenericResponse
}

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private lateinit var apiService: AdminApiService
    private val scope = CoroutineScope(Dispatchers.Main + Job())

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        val retrofit = Retrofit.Builder()
            .baseUrl("http://your-admin-panel-url.com/")
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        apiService = retrofit.create(AdminApiService::class.java)

        binding.rvDevices.layoutManager = LinearLayoutManager(this)
        refreshDevices()
    }

    private fun refreshDevices() {
        scope.launch {
            try {
                val devices = apiService.getDevices()
                binding.rvDevices.adapter = DeviceAdapter(devices) { device, command ->
                    sendCommand(device.device_id, command)
                }
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Error: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun sendCommand(deviceId: String, command: String) {
        scope.launch {
            try {
                val response = apiService.sendCommand(deviceId, command)
                Toast.makeText(this@MainActivity, response.message, Toast.LENGTH_SHORT).show()
                refreshDevices()
            } catch (e: Exception) {
                Toast.makeText(this@MainActivity, "Failed: ${e.message}", Toast.LENGTH_SHORT).show()
            }
        }
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

            holder.binding.btnLock.setOnClickListener { onAction(device, "lock") }
            holder.binding.btnUnlock.setOnClickListener { onAction(device, "unlock") }
            holder.binding.btnApps.setOnClickListener { onAction(device, "hide_app") }
        }

        override fun getItemCount() = devices.size
    }
}
