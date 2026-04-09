package com.example.phonerental.user

import android.app.admin.DevicePolicyManager
import android.content.BroadcastReceiver
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.graphics.Color
import android.os.Build
import android.os.Bundle
import android.os.CountDownTimer
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.example.phonerental.user.databinding.ActivityMainBinding
import java.text.SimpleDateFormat
import java.util.*

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private lateinit var devicePolicyManager: DevicePolicyManager
    private lateinit var adminComponent: ComponentName
    private var countDownTimer: CountDownTimer? = null

    private val refreshReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
            if (intent?.action == BeaconService.ACTION_SHOW_MESSAGE) {
                val message = intent.getStringExtra(BeaconService.EXTRA_MESSAGE)
                if (message != null) {
                    com.google.android.material.dialog.MaterialAlertDialogBuilder(this@MainActivity)
                        .setTitle("System Message")
                        .setMessage(message)
                        .setPositiveButton("OK", null)
                        .show()
                }
            } else {
                updateUI()
            }
        }
    }

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

        binding.btnSupport.setOnClickListener {
            try {
                val intent = Intent(Intent.ACTION_DIAL)
                // In a real app, this would be a configured support number
                intent.data = android.net.Uri.parse("tel:1234567890")
                startActivity(intent)
            } catch (e: Exception) {
                Toast.makeText(this, "Unable to make a call. Please contact support via other means.", Toast.LENGTH_LONG).show()
            }
        }

        startService(Intent(this, BeaconService::class.java))
        updateUI()
    }

    override fun onResume() {
        super.onResume()
        val filter = IntentFilter()
        filter.addAction(BeaconService.ACTION_REFRESH_UI)
        filter.addAction(BeaconService.ACTION_SHOW_MESSAGE)

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            registerReceiver(refreshReceiver, filter, Context.RECEIVER_NOT_EXPORTED)
        } else {
            registerReceiver(refreshReceiver, filter)
        }
        updateUI()
    }

    override fun onPause() {
        super.onPause()
        try {
            unregisterReceiver(refreshReceiver)
        } catch (e: Exception) {
            // Receiver might not be registered
        }
    }

    private fun updateUI() {
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        val endTimeStr = prefs.getString("rental_end", null)
        val isLocked = prefs.getBoolean("is_locked", false)

        var showLockedOverlay = isLocked

        if (isLocked) {
            binding.tvStatus.text = "LOCKED"
            binding.tvStatus.setTextColor(Color.parseColor("#ef4444"))
            enterKioskMode()
        } else {
            binding.tvStatus.text = "ACTIVE"
            binding.tvStatus.setTextColor(Color.parseColor("#10B981"))
            exitKioskMode()
        }

        if (!endTimeStr.isNullOrEmpty()) {
            try {
                val sdf = SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault())
                val endDate = sdf.parse(endTimeStr)
                if (endDate != null) {
                    val currentTime = System.currentTimeMillis()
                    val diff = endDate.time - currentTime

                    if (diff > 0) {
                        startTimer(diff)
                    } else {
                        binding.tvTimer.text = "EXPIRED"
                        binding.tvStatus.text = "EXPIRED"
                        binding.tvStatus.setTextColor(Color.parseColor("#f43f5e"))
                        showLockedOverlay = true
                        enterKioskMode()
                    }
                } else {
                    binding.tvTimer.text = "--:--:--"
                }
            } catch (e: Exception) {
                binding.tvTimer.text = "--:--:--"
            }
        } else {
            binding.tvTimer.text = "NOT SET"
        }

        if (devicePolicyManager.isAdminActive(adminComponent)) {
            binding.btnAdmin.visibility = View.GONE

            // Check if Device Owner (required for hiding apps)
            if (!devicePolicyManager.isDeviceOwnerApp(packageName)) {
                binding.tvLabel.text = "REMAINING TIME\n(Warning: Not Device Owner)"
                binding.tvLabel.setTextColor(Color.parseColor("#f59e0b"))
            } else {
                binding.tvLabel.text = "REMAINING TIME"
                binding.tvLabel.setTextColor(Color.parseColor("#64748b"))
            }
        } else {
            binding.btnAdmin.visibility = View.VISIBLE
        }

        if (showLockedOverlay) {
            showLockedOverlay()
        } else {
            hideLockedOverlay()
        }
    }

    private fun showLockedOverlay() {
        binding.btnAdmin.visibility = View.GONE
        binding.btnSupport.visibility = View.VISIBLE
        // Add a support message
        binding.tvLabel.text = "DEVICE LOCKED\nPlease contact support to renew your rental."
        binding.tvLabel.setTextColor(Color.parseColor("#ef4444"))
        binding.root.setBackgroundColor(Color.parseColor("#fee2e2"))
    }

    private fun hideLockedOverlay() {
        binding.btnSupport.visibility = View.GONE
        binding.root.setBackgroundColor(Color.parseColor("#F8FAFC"))
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
                binding.tvStatus.text = "EXPIRED"
                binding.tvStatus.setTextColor(Color.parseColor("#ef4444"))
                enterKioskMode()
            }
        }.start()
    }

    private fun enterKioskMode() {
        if (devicePolicyManager.isAdminActive(adminComponent)) {
            try {
                // For simplicity, we use lockNow to immediately block access
                // Real kiosk mode would use startLockTask() but needs Device Owner
                devicePolicyManager.lockNow()
            } catch (e: SecurityException) {
                //Toast.makeText(this, "Admin not active", Toast.LENGTH_SHORT).show()
            }
        }
    }

    private fun exitKioskMode() {
        // Just clear flags or stop lock tasks if they were active
    }

    override fun onBackPressed() {
        val prefs = getSharedPreferences("rental_prefs", Context.MODE_PRIVATE)
        if (prefs.getBoolean("is_locked", false)) {
            return
        }
        super.onBackPressed()
    }
}
