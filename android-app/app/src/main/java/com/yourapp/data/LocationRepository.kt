package com.yourapp.data

import com.yourapp.data.dao.JobLogDao
import com.yourapp.data.dao.LocationDao
import com.yourapp.data.entity.JobLocation
import com.yourapp.data.entity.JobLog
import kotlinx.coroutines.flow.Flow

class LocationRepository(
    private val locationDao: LocationDao,
    private val jobLogDao: JobLogDao
) {
    val allLocations: Flow<List<JobLocation>> = locationDao.getAllLocations()

    suspend fun insertLocations(locations: List<JobLocation>) {
        locationDao.insertLocations(locations)
    }

    suspend fun insertJobLog(log: JobLog) {
        jobLogDao.insertLog(log)
    }
}
