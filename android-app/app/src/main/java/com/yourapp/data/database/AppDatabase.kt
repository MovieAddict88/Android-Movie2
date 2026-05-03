package com.yourapp.data.database

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import com.yourapp.data.dao.JobLogDao
import com.yourapp.data.dao.LocationDao
import com.yourapp.data.entity.JobLocation
import com.yourapp.data.entity.JobLog

@Database(entities = [JobLocation::class, JobLog::class], version = 1, exportSchema = false)
abstract class AppDatabase : RoomDatabase() {
    abstract fun locationDao(): LocationDao
    abstract fun jobLogDao(): JobLogDao

    companion object {
        @Volatile
        private var INSTANCE: AppDatabase? = null

        fun getDatabase(context: Context): AppDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "field_service_db"
                ).build()
                INSTANCE = instance
                instance
            }
        }
    }
}
