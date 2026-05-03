package com.yourapp.data.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "job_logs")
data class JobLog(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val locationId: Long,
    val checkInAt: Long,
    val checkOutAt: Long? = null,
    val notes: String? = null,
    val photoPath: String? = null,
    val isSynced: Boolean = false
)
