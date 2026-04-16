package com.example.autobetter

import android.util.Log

object AutomationManager {
    var isRecording = false
    var isPlaying = false
    private var hasTriggeredInCurrentCycle = false

    private val recordedActions = mutableListOf<RecordedAction>()

    fun clearActions() {
        recordedActions.clear()
    }

    fun recordAction(x: Float, y: Float) {
        if (isRecording) {
            recordedActions.add(RecordedAction(x, y))
        }
    }

    fun onTextDetected(text: String) {
        if (isPlaying) {
            val isTriggerDetected = text.contains("Place your bets", ignoreCase = true)

            if (isTriggerDetected && !hasTriggeredInCurrentCycle) {
                hasTriggeredInCurrentCycle = true
                replayActions()
            } else if (!isTriggerDetected) {
                // Reset trigger if "Place your bets" is no longer visible
                hasTriggeredInCurrentCycle = false
            }
        }
    }

    private fun replayActions() {
        val service = AutoBetterAccessibilityService.instance ?: return

        // Run on a separate thread to avoid blocking the main thread (and OCR)
        Thread {
            for (action in recordedActions) {
                if (!isPlaying) break
                service.click(action.x, action.y)
                try {
                    Thread.sleep(500) // Increased delay for stability
                } catch (e: InterruptedException) {
                    break
                }
            }
        }.start()
    }

    data class RecordedAction(val x: Float, val y: Float)
}
