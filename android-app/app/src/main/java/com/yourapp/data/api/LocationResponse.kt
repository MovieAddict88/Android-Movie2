package com.yourapp.data.api

data class LocationResponse(
    val id: Long,
    val company_id: Long,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radius_meters: Int
)
