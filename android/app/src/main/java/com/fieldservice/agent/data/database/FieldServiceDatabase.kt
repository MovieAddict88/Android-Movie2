package com.fieldservice.agent.data.database

import androidx.room.Database
import androidx.room.RoomDatabase
import com.fieldservice.agent.data.dao.JobLogDao
import com.fieldservice.agent.data.dao.LocationDao
import com.fieldservice.agent.data.entity.JobLogEntity
import com.fieldservice.agent.data.entity.LocationEntity

@Database(
    entities = [
        LocationEntity::class,
        JobLogEntity::class
    ],
    version = 1,
    exportSchema = true
)
abstract class FieldServiceDatabase : RoomDatabase() {
    abstract fun locationDao(): LocationDao
    abstract fun jobLogDao(): JobLogDao

    companion object {
        const val DATABASE_NAME = "field_service_db"

        val MIGRATIONS = listOf(
            // Add migration callbacks here when schema changes
            // Example:
            // Migration from 1 to 2:
            // object : Migration(1, 2) {
            //     override fun migrate(it: SupportSQLiteDatabase) {
            //         it.execSQL("ALTER TABLE locations ADD COLUMN new_column TEXT")
            //     }
            // }
        )
    }
}
