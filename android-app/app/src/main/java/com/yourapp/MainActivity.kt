package com.yourapp

import android.os.Bundle
import androidx.activity.compose.setContent
import androidx.activity.viewModels
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import com.yourapp.data.LocationRepository
import com.yourapp.data.database.AppDatabase
import com.yourapp.ui.HomeScreen
import com.yourapp.ui.MainViewModel
import com.yourapp.ui.MainViewModelFactory
import androidx.biometric.BiometricPrompt
import androidx.core.content.ContextCompat
import androidx.fragment.app.FragmentActivity
import java.util.concurrent.Executor

class MainActivity : FragmentActivity() {
    private lateinit var executor: Executor
    private lateinit var biometricPrompt: BiometricPrompt
    private lateinit var promptInfo: BiometricPrompt.PromptInfo

    private val viewModel: MainViewModel by viewModels {
        val database = AppDatabase.getDatabase(applicationContext)
        MainViewModelFactory(LocationRepository(database.locationDao(), database.jobLogDao()))
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        setupBiometric()

        setContent {
            MaterialTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    val locations by viewModel.locations.collectAsState()
                    HomeScreen(locations = locations) { location ->
                        biometricPrompt.authenticate(promptInfo)
                        // In a real app, you'd handle the success in the callback
                        // and then call viewModel.completeJob(location, "Completed via UI")
                    }
                }
            }
        }
    }

    private fun setupBiometric() {
        executor = ContextCompat.getMainExecutor(this)
        biometricPrompt = BiometricPrompt(this, executor,
            object : BiometricPrompt.AuthenticationCallback() {
                override fun onAuthenticationSucceeded(result: BiometricPrompt.AuthenticationResult) {
                    super.onAuthenticationSucceeded(result)
                    // Success logic
                }
            })

        promptInfo = BiometricPrompt.PromptInfo.Builder()
            .setTitle("Biometric login for job completion")
            .setSubtitle("Confirm your identity to finish the job")
            .setNegativeButtonText("Cancel")
            .build()
    }
}
