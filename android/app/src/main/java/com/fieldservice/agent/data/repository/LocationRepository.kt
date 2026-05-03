package com.fieldservice.agent.data.repository

import com.fieldservice.agent.data.dao.LocationDao
import com.fieldservice.agent.data.entity.LocationEntity
import com.fieldservice.agent.data.remote.api.*
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

    suspend fun refreshLocations(
        status: String? = null,
        date: String? = null,
        search: String? = null,
        sortBy: String? = null,
        sortOrder: String? = null,
        page: Int = 1,
        perPage: Int = 50
    ): NetworkResult<PaginatedLocationsResult> {
        return try {
            val response = apiService.getLocations(
                status = status,
                date = date,
                activeOnly = false,
                search = search,
                sortBy = sortBy,
                sortOrder = sortOrder,
                page = page,
                perPage = perPage
            )

            if (response.success && response.locations != null) {
                val entities = response.locations.map { dto -> dto.toEntity() }
                
                // Save to local database
                locationDao.insertLocations(entities)
                
                val pagination = response.pagination?.let {
                    PaginationResult(
                        currentPage = it.currentPage,
                        lastPage = it.lastPage,
                        perPage = it.perPage,
                        total = it.total,
                        hasMore = it.hasMore
                    )
                }
                
                NetworkResult.Success(PaginatedLocationsResult(entities, pagination))
            } else {
                NetworkResult.Error(response.message ?: "Failed to fetch locations")
            }
        } catch (e: Exception) {
            NetworkResult.Error(e.message ?: "Network error occurred")
        }
    }

    suspend fun syncLocations(): NetworkResult<List<LocationEntity>> {
        return try {
            val response = apiService.getLocations()
            if (response.success && response.locations != null) {
                val entities = response.locations.map { dto -> dto.toEntity() }
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
        try {
            // Update locally first for offline support
            locationDao.updateStatus(id, status)
            
            // Then sync to server
            val response = apiService.updateLocationStatus(id, UpdateStatusRequest(status))
            if (!response.success) {
                // Log error but don't fail - will sync later
            }
        } catch (e: Exception) {
            // Network error - status update will be synced later
        }
    }

    suspend fun getLocationsInBounds(
        minLat: Double,
        maxLat: Double,
        minLng: Double,
        maxLng: Double
    ): List<LocationEntity> = locationDao.getLocationsInBounds(minLat, maxLat, minLng, maxLng)

    fun getCountByStatus(status: String): Flow<Int> = locationDao.getCountByStatus(status)
    
    suspend fun getLocationDetail(id: Int): NetworkResult<LocationDetailDto> {
        return try {
            val response = apiService.getLocation(id)
            if (response.success && response.data != null) {
                NetworkResult.Success(response.data)
            } else {
                NetworkResult.Error(response.message ?: "Failed to fetch location details")
            }
        } catch (e: Exception) {
            // Try to get from local cache
            val cached = locationDao.getLocationById(id)
            if (cached != null) {
                NetworkResult.Success(cached.toDetailDto())
            } else {
                NetworkResult.Error(e.message ?: "Network error")
            }
        }
    }
}

data class PaginatedLocationsResult(
    val locations: List<LocationEntity>,
    val pagination: PaginationResult?
)

data class PaginationResult(
    val currentPage: Int,
    val lastPage: Int,
    val perPage: Int,
    val total: Int,
    val hasMore: Boolean
)

fun LocationDto.toEntity(): LocationEntity = LocationEntity(
    id = id,
    name = name,
    address = address,
    latitude = latitude,
    longitude = longitude,
    radius = radius,
    status = status,
    statusLabel = statusLabel ?: status.replace("_", " ").replaceFirstChar { it.uppercase() },
    notes = notes,
    scheduledDate = scheduledDate,
    scheduledTimeStart = scheduledTimeStart,
    scheduledTimeEnd = scheduledTimeEnd,
    googleMapsUrl = googleMapsUrl,
    companyName = companyName
)

fun LocationEntity.toDetailDto(): LocationDetailDto = LocationDetailDto(
    id = id,
    name = name,
    address = address,
    latitude = latitude,
    longitude = longitude,
    radius = radius,
    status = status,
    statusLabel = statusLabel,
    notes = notes,
    scheduledDate = scheduledDate,
    scheduledTimeStart = scheduledTimeStart,
    scheduledTimeEnd = scheduledTimeEnd,
    googleMapsUrl = googleMapsUrl,
    metadata = null,
    company = companyName?.let { CompanyDto(0, it) },
    assignedWorker = null,
    recentJobs = null,
    createdAt = null,
    updatedAt = null
)