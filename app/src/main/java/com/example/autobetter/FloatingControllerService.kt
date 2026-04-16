package com.example.autobetter

import android.app.Service
import android.content.Intent
import android.graphics.PixelFormat
import android.os.IBinder
import android.view.Gravity
import android.view.LayoutInflater
import android.view.View
import android.view.WindowManager
import android.widget.Button

class FloatingControllerService : Service() {

    private lateinit var windowManager: WindowManager
    private lateinit var floatingView: View
    private var recordOverlay: View? = null

    override fun onBind(intent: Intent?): IBinder? = null

    override fun onCreate() {
        super.onCreate()
        windowManager = getSystemService(WINDOW_SERVICE) as WindowManager
        floatingView = LayoutInflater.from(this).inflate(R.layout.floating_control, null)

        val params = WindowManager.LayoutParams(
            WindowManager.LayoutParams.WRAP_CONTENT,
            WindowManager.LayoutParams.WRAP_CONTENT,
            WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY,
            WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE,
            PixelFormat.TRANSLUCENT
        )

        params.gravity = Gravity.TOP or Gravity.START
        params.x = 0
        params.y = 100

        windowManager.addView(floatingView, params)

        floatingView.findViewById<Button>(R.id.btn_record).setOnClickListener {
            startRecording()
        }

        floatingView.findViewById<Button>(R.id.btn_stop).setOnClickListener {
            stopRecording()
            AutomationManager.isPlaying = false
        }

        floatingView.findViewById<Button>(R.id.btn_play).setOnClickListener {
            AutomationManager.isPlaying = true
        }
    }

    private fun startRecording() {
        AutomationManager.isRecording = true
        AutomationManager.clearActions()

        val params = WindowManager.LayoutParams(
            WindowManager.LayoutParams.MATCH_PARENT,
            WindowManager.LayoutParams.MATCH_PARENT,
            WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY,
            WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or WindowManager.LayoutParams.FLAG_NOT_TOUCH_MODAL or WindowManager.LayoutParams.FLAG_WATCH_OUTSIDE_TOUCH,
            PixelFormat.TRANSLUCENT
        )

        recordOverlay = View(this)
        recordOverlay?.setOnTouchListener { v, event ->
            if (event.action == android.view.MotionEvent.ACTION_DOWN) {
                AutomationManager.recordAction(event.rawX, event.rawY)
            }
            false
        }
        windowManager.addView(recordOverlay, params)
    }

    private fun stopRecording() {
        AutomationManager.isRecording = false
        recordOverlay?.let {
            windowManager.removeView(it)
            recordOverlay = null
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        if (::floatingView.isInitialized) {
            windowManager.removeView(floatingView)
        }
    }
}
