package com.fieldservice.agent.data.entity

import androidx.room.Entity
import androidx.room.PrimaryKey
import com.google.gson.annotations.SerializedName

@Entity(tableName = "locations")
data class LocationEntity(
    @PrimaryKey
    val id: Int,
    val name: String,
    val address: String,
    val latitude: Double,
    val longitude: Double,
    val radius: Int,
    val status: String,
    val statusLabel: String,
    val notes: String?,
    val scheduledDate: String?,
    val scheduledTimeStart: String?,
    val scheduledTimeEnd: String?,
    val googleMapsUrl: String?,
    val companyName: String,
    val isSynced: Boolean = true,
    val lastUpdated: Long = System.currentTimeMillis()
)
