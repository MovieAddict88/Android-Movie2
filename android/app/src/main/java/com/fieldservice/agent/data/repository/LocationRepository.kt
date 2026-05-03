package com.fieldservice.agent.data.repository

import com.fieldservice.agent.data.dao.LocationDao
import com.fieldservice.agent.data.entity.LocationEntity
import com.fieldservice.agent.data.remote.ApiService
import com.fieldservice.agent.util.NetworkResult
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class LocationRepository @Inject constructor(
    private val locationDao: LocationDao,
    private val apiService: ApiService
) {
    fun getAllLocations(): Flow<List<LocationEntity>> = locationDao.getAllLocations()

    fun getActiveLocations(): Flow<List<LocationEntity>> = locationDao.getActiveLocations()

    fun getTodayLocations(date: String): Flow<List<LocationEntity>> =
        locationDao.getTodayLocations(date)

    fun getLocationById(id: Int): Flow<LocationEntity?> = locationDao.getLocationByIdFlow(id)

    suspend fun getLocationByIdOnce(id: Int): LocationEntity? = locationDao.getLocationById(id)

    suspend fun syncLocations(): NetworkResult<List<LocationEntity>> {
        return try {
            val response = apiService.getLocations()
            if (response.success && response.locations != null) {
                val entities = response.locations.map { dto ->
                    LocationEntity(
                        id = dto.id,
                        name = dto.name,
                        address = dto.address,
                        latitude = dto.latitude,
                        longitude = dto.longitude,
                        radius = dto.radius,
                        status = dto.status,
                        statusLabel = dto.statusLabel,
                        notes = dto.notes,
                        scheduledDate = dto.scheduledDate,
                        scheduledTimeStart = dto.scheduledTimeStart,
                        scheduledTimeEnd = dto.scheduledTimeEnd,
                        googleMapsUrl = dto.googleMapsUrl,
                        companyName = dto.companyName
                    )
                }
                locationDao.insertLocations(entities)
                NetworkResult.Success(entities)
            } else {
                NetworkResult.Error(response.message ?: "Sync failed")
            }
        } catch (e: Exception) {
            NetworkResult.Error(e.message ?: "Network error")
        }
    }

    suspend fun updateLocationStatus(id: Int, status: String) {
        locationDao.updateStatus(id, status)
    }

    suspend fun getLocationsInBounds(
        minLat: Double,
        maxLat: Double,
        minLng: Double,
        maxLng: Double
    ): List<LocationEntity> = locationDao.getLocationsInBounds(minLat, maxLat, minLng, maxLng)

    fun getCountByStatus(status: String): Flow<Int> = locationDao.getCountByStatus(status)
}
