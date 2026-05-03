package com.fieldservice.agent.data.dao

import androidx.room.*
import com.fieldservice.agent.data.entity.LocationEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface LocationDao {

    @Query("SELECT * FROM locations ORDER BY scheduledDate ASC, scheduledTimeStart ASC")
    fun getAllLocations(): Flow<List<LocationEntity>>

    @Query("SELECT * FROM locations WHERE status IN ('pending', 'assigned', 'in_progress') ORDER BY scheduledDate ASC")
    fun getActiveLocations(): Flow<List<LocationEntity>>

    @Query("SELECT * FROM locations WHERE scheduledDate = :date AND status IN ('pending', 'assigned', 'in_progress') ORDER BY scheduledTimeStart ASC")
    fun getTodayLocations(date: String): Flow<List<LocationEntity>>

    @Query("SELECT * FROM locations WHERE id = :id")
    suspend fun getLocationById(id: Int): LocationEntity?

    @Query("SELECT * FROM locations WHERE id = :id")
    fun getLocationByIdFlow(id: Int): Flow<LocationEntity?>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertLocations(locations: List<LocationEntity>)

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertLocation(location: LocationEntity)

    @Update
    suspend fun updateLocation(location: LocationEntity)

    @Query("UPDATE locations SET status = :status WHERE id = :id")
    suspend fun updateStatus(id: Int, status: String)

    @Delete
    suspend fun deleteLocation(location: LocationEntity)

    @Query("DELETE FROM locations")
    suspend fun deleteAllLocations()

    @Query("SELECT * FROM locations WHERE latitude BETWEEN :minLat AND :maxLat AND longitude BETWEEN :minLng AND :maxLng")
    suspend fun getLocationsInBounds(minLat: Double, maxLat: Double, minLng: Double, maxLng: Double): List<LocationEntity>

    @Query("SELECT COUNT(*) FROM locations WHERE status = :status")
    fun getCountByStatus(status: String): Flow<Int>
}
