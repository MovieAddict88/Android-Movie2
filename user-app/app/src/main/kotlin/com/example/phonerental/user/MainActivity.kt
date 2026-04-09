package com.example.phonerental.user

import android.app.admin.DevicePolicyManager
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.os.CountDownTimer
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.example.phonerental.user.databinding.ActivityMainBinding
import java.text.SimpleDateFormat
import java.util.*

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private lateinit var devicePolicyManager: DevicePolicyManager
    private lateinit var adminComponent: ComponentName
    private var countDownTimer: CountDownTimer? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        devicePolicyManager = getSystemService(Context.DEVICE_POLICY_SERVICE) as DevicePolicyManager
        adminComponent = ComponentName(this, DeviceAdminReceiver::class.java)

        binding.btnAdmin.setOnClickListener {
            val intent = Intent(DevicePolicyManager.ACTION_ADD_DEVICE_ADMIN)
            intent.putExtra(DevicePolicyManager.EXTRA_DEVICE_ADMIN, adminComponent)
            intent.putExtra(DevicePolicyManager.EXTRA_ADD_EXPLANATION, "Admin permission is required to manage the rental period.")
            startActivityForResult(intent, 1)
        }

        startService(Intent(this, BeaconService::class.java))
        updateUI()
    }

    private fun updateUI() {
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        val endTimeStr = prefs.getString("rental_end", null)
        val isLocked = prefs.getBoolean("is_locked", false)

        if (isLocked) {
            enterKioskMode()
        }

        if (endTimeStr != null) {
            try {
                val sdf = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                val endDate = sdf.parse(endTimeStr)
                val currentTime = System.currentTimeMillis()
                val diff = endDate.time - currentTime

                if (diff > 0) {
                    startTimer(diff)
                } else {
                    binding.tvTimer.text = "EXPIRED"
                    enterKioskMode()
                }
            } catch (e: Exception) {
                binding.tvTimer.text = "--:--:--"
            }
        }
    }

    private fun startTimer(duration: Long) {
        countDownTimer?.cancel()
        countDownTimer = object : CountDownTimer(duration, 1000) {
            override fun onTick(millisUntilFinished: Long) {
                val hours = millisUntilFinished / (1000 * 60 * 60)
                val minutes = (millisUntilFinished / (1000 * 60)) % 60
                val seconds = (millisUntilFinished / 1000) % 60
                binding.tvTimer.text = String.format("%02d:%02d:%02d", hours, minutes, seconds)
            }

            override fun onFinish() {
                binding.tvTimer.text = "EXPIRED"
                enterKioskMode()
            }
        }.start()
    }

    private fun enterKioskMode() {
        if (devicePolicyManager.isAdminActive(adminComponent)) {
            // In a real device owner app, we'd use startLockTask()
            // For simple admin, we can force lock
            try {
                devicePolicyManager.lockNow()
                Toast.makeText(this, "Rental Expired - Device Locked", Toast.LENGTH_LONG).show()
            } catch (e: SecurityException) {
                Toast.makeText(this, "Admin not active", Toast.LENGTH_SHORT).show()
            }
        }
    }

    override fun onBackPressed() {
        // Disable back button if locked/expired
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        if (prefs.getBoolean("is_locked", false)) {
            return
        }
        super.onBackPressed()
    }
}
