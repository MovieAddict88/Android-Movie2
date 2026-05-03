package com.yourapp.data.dao

import androidx.room.*
import com.yourapp.data.entity.JobLocation
import kotlinx.coroutines.flow.Flow

@Dao
interface LocationDao {
    @Query("SELECT * FROM job_locations")
    fun getAllLocations(): Flow<List<JobLocation>>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertLocations(locations: List<JobLocation>)

    @Query("SELECT * FROM job_locations WHERE id = :id")
    suspend fun getLocationById(id: Long): JobLocation?

    @Query("DELETE FROM job_locations")
    suspend fun deleteAll()
}
