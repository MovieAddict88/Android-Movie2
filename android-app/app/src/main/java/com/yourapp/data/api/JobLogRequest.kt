package com.yourapp.data.api

data class JobLogRequest(
    val location_id: Long,
    val check_in_at: String,
    val check_out_at: String,
    val notes: String? = null,
    val photo_base64: String? = null
)
