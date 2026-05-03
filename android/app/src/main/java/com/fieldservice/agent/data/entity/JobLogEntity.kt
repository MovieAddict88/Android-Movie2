package com.fieldservice.agent.data.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "job_logs")
data class JobLogEntity(
    @PrimaryKey(autoGenerate = true)
    val id: Int = 0,
    val syncId: String,
    val locationId: Int,
    val locationName: String,
    val locationAddress: String,
    val status: String,
    val startedAt: Long,
    val completedAt: Long?,
    val latitudeStart: Double?,
    val longitudeStart: Double?,
    val latitudeEnd: Double?,
    val longitudeEnd: Double?,
    val notes: String?,
    val photoBase64: String?,
    val isSynced: Boolean = false,
    val createdAt: Long = System.currentTimeMillis()
)
