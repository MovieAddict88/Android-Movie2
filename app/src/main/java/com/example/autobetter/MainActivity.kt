package com.example.autobetter

import android.content.Context
import android.content.Intent
import android.media.projection.MediaProjectionManager
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity

class MainActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)

        // Request Media Projection
        val projectionManager = getSystemService(Context.MEDIA_PROJECTION_SERVICE) as MediaProjectionManager
        startActivityForResult(projectionManager.createScreenCaptureIntent(), 100)

        // Start Floating Service
        startService(Intent(this, FloatingControllerService::class.java))
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == 100 && resultCode == RESULT_OK && data != null) {
            val serviceIntent = Intent(this, ScreenCaptureService::class.java)
            serviceIntent.putExtra("RESULT_CODE", resultCode)
            serviceIntent.putExtra("RESULT_DATA", data)
            startService(serviceIntent)
        }
    }
}
