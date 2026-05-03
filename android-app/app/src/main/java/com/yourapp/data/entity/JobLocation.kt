package com.yourapp.data.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "job_locations")
data class JobLocation(
    @PrimaryKey val id: Long,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radiusMeters: Int
)
