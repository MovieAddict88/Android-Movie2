package com.example.carrental.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.carrental.api.RetrofitClient
import com.example.carrental.model.BaseResponse
import com.example.carrental.model.Payment
import com.example.carrental.model.UpdatePaymentStatusRequest
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody

class PaymentViewModel : ViewModel() {
    private val _payments = MutableStateFlow<List<Payment>>(emptyList())
    val payments = _payments.asStateFlow()

    private val _loading = MutableStateFlow(false)
    val loading = _loading.asStateFlow()

    private val _submissionStatus = MutableStateFlow<String?>(null)
    val submissionStatus = _submissionStatus.asStateFlow()

    private val apiService = RetrofitClient.instance

    fun fetchAllPayments() {
        viewModelScope.launch {
            _loading.value = true
            try {
                val response = apiService.getAllPayments()
                if (response.isSuccessful) {
                    _payments.value = response.body()?.payments ?: emptyList()
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                _loading.value = false
            }
        }
    }

    fun submitPayment(
        bookingId: Int,
        paymentMethod: String,
        referenceNumber: String,
        amount: Double,
        proofOfPayment: MultipartBody.Part?
    ) {
        viewModelScope.launch {
            _loading.value = true
            try {
                val bookingIdBody = bookingId.toString().toRequestBody("text/plain".toMediaTypeOrNull())
                val methodBody = paymentMethod.toRequestBody("text/plain".toMediaTypeOrNull())
                val refBody = referenceNumber.toRequestBody("text/plain".toMediaTypeOrNull())
                val amountBody = amount.toString().toRequestBody("text/plain".toMediaTypeOrNull())

                val response = apiService.submitPayment(
                    bookingIdBody,
                    methodBody,
                    refBody,
                    amountBody,
                    proofOfPayment
                )
                if (response.isSuccessful) {
                    _submissionStatus.value = "success"
                } else {
                    _submissionStatus.value = "error: ${response.message()}"
                }
            } catch (e: Exception) {
                _submissionStatus.value = "error: ${e.message}"
            } finally {
                _loading.value = false
            }
        }
    }

    fun updatePaymentStatus(paymentId: Int, action: String, reason: String?) {
        viewModelScope.launch {
            try {
                val response = apiService.updatePaymentStatus(
                    UpdatePaymentStatusRequest(paymentId, action, reason)
                )
                if (response.isSuccessful) {
                    fetchAllPayments()
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }

    fun resetSubmissionStatus() {
        _submissionStatus.value = null
    }
}
