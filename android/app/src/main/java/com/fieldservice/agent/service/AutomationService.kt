package com.fieldservice.agent.service

import android.accessibilityservice.AccessibilityService
import android.accessibilityservice.AccessibilityServiceInfo
import android.content.Intent
import android.graphics.Rect
import android.os.Build
import android.util.Log
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import androidx.core.accessibility.ExperimentalAccessibilityApi
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow

class AutomationService : AccessibilityService() {

    private val _isEnabled = MutableStateFlow(false)
    val isEnabled: StateFlow<Boolean> = _isEnabled

    override fun onServiceConnected() {
        super.onServiceConnected()
        _isEnabled.value = true
        Log.d(TAG, "Automation service connected")

        serviceInfo = AccessibilityServiceInfo().apply {
            eventTypes = AccessibilityEvent.TYPE_ALL
            feedbackType = AccessibilityServiceInfo.FEEDBACK_GENERIC
            flags = AccessibilityServiceInfo.FLAG_RETRIEVE_INTERACTIVE_WINDOWS or
                    AccessibilityServiceInfo.FLAG_REPORT_VIEW_IDS
            notificationTimeout = 100
        }
    }

    override fun onAccessibilityEvent(event: AccessibilityEvent?) {
        if (event == null) return

        when (event.eventType) {
            AccessibilityEvent.TYPE_WINDOW_STATE_CHANGED -> {
                Log.d(TAG, "Window state changed: ${event.className}")
            }
            AccessibilityEvent.TYPE_WINDOW_CONTENT_CHANGED -> {
                Log.d(TAG, "Window content changed")
            }
            AccessibilityEvent.TYPE_VIEW_CLICKED -> {
                Log.d(TAG, "View clicked: ${event.source?.text}")
            }
        }
    }

    override fun onInterrupt() {
        Log.w(TAG, "Accessibility service interrupted")
        _isEnabled.value = false
    }

    override fun onDestroy() {
        super.onDestroy()
        _isEnabled.value = false
        Log.d(TAG, "Automation service destroyed")
    }

    fun findNodeByText(text: String, exactMatch: Boolean = false): AccessibilityNodeInfo? {
        val rootNode = rootInActiveWindow ?: return null
        return findNodeByTextRecursive(rootNode, text, exactMatch)
    }

    fun findNodeByViewId(viewId: String): AccessibilityNodeInfo? {
        val rootNode = rootInActiveWindow ?: return null
        return rootNode.findAccessibilityNodeInfosByViewId(viewId).firstOrNull()
    }

    fun findNodesByText(text: String): List<AccessibilityNodeInfo> {
        val rootNode = rootInActiveWindow ?: return emptyList()
        val results = mutableListOf<AccessibilityNodeInfo>()
        findNodesByTextRecursive(rootNode, text, results)
        return results
    }

    private fun findNodeByTextRecursive(
        node: AccessibilityNodeInfo,
        text: String,
        exactMatch: Boolean
    ): AccessibilityNodeInfo? {
        val nodeText = node.text?.toString()
        if (nodeText != null) {
            val matches = if (exactMatch) nodeText == text else nodeText.contains(text)
            if (matches) {
                return node
            }
        }

        for (i in 0 until node.childCount) {
            val child = node.getChild(i) ?: continue
            val result = findNodeByTextRecursive(child, text, exactMatch)
            if (result != null) {
                return result
            }
        }

        return null
    }

    private fun findNodesByTextRecursive(
        node: AccessibilityNodeInfo,
        text: String,
        results: MutableList<AccessibilityNodeInfo>
    ) {
        val nodeText = node.text?.toString()
        if (nodeText != null && nodeText.contains(text)) {
            results.add(node)
        }

        for (i in 0 until node.childCount) {
            val child = node.getChild(i) ?: continue
            findNodesByTextRecursive(child, text, results)
        }
    }

    fun clickNode(node: AccessibilityNodeInfo): Boolean {
        return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
        } else {
            val bounds = Rect()
            node.getBoundsInScreen(bounds)
            val centerX = bounds.centerX()
            val centerY = bounds.centerY()
            val gestureResult = GestureExecutor.performClick(this, centerX, centerY)
            gestureResult
        }
    }

    fun clickByText(text: String, exactMatch: Boolean = false): Boolean {
        val node = findNodeByText(text, exactMatch)
        return if (node != null) {
            clickNode(node)
        } else {
            false
        }
    }

    fun clickByViewId(viewId: String): Boolean {
        val node = findNodeByViewId(viewId)
        return if (node != null) {
            clickNode(node)
        } else {
            false
        }
    }

    fun setText(node: AccessibilityNodeInfo, text: String): Boolean {
        return node.performAction(AccessibilityNodeInfo.ACTION_SET_TEXT, android.os.Bundle().apply {
            putCharSequence(AccessibilityNodeInfo.ACTION_ARGUMENT_SET_TEXT_CHARSEQUENCE, text)
        })
    }

    fun setTextByViewId(viewId: String, text: String): Boolean {
        val node = findNodeByViewId(viewId) ?: return false
        return setText(node, text)
    }

    fun scrollToNode(node: AccessibilityNodeInfo): Boolean {
        return node.performAction(AccessibilityNodeInfo.ACTION_SCROLL_FORWARD) ||
                node.performAction(AccessibilityNodeInfo.ACTION_SCROLL_BACKWARD)
    }

    fun scrollDown(): Boolean {
        val rootNode = rootInActiveWindow ?: return false
        return rootNode.performAction(AccessibilityNodeInfo.ACTION_SCROLL_FORWARD)
    }

    fun scrollUp(): Boolean {
        val rootNode = rootInActiveWindow ?: return false
        return rootNode.performAction(AccessibilityNodeInfo.ACTION_SCROLL_BACKWARD)
    }

    fun takeScreenshot(): AccessibilityNodeInfo? {
        return rootInActiveWindow?.clone()
    }

    fun isAutomationEnabled(): Boolean = _isEnabled.value

    companion object {
        private const val TAG = "AutomationService"
        var instance: AutomationService? = null
            private set
    }

    init {
        instance = this
    }
}
