package com.fieldservice.agent.di

import android.content.Context
import androidx.room.Room
import com.fieldservice.agent.BuildConfig
import com.fieldservice.agent.data.dao.JobLogDao
import com.fieldservice.agent.data.dao.LocationDao
import com.fieldservice.agent.data.database.FieldServiceDatabase
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.repository.AuthRepository
import com.fieldservice.agent.service.GeofenceHelper
import com.fieldservice.agent.util.NetworkConnectivityObserver
import com.google.gson.Gson
import com.google.gson.GsonBuilder
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object AppModule {

    @Provides
    @Singleton
    fun provideDatabase(@ApplicationContext context: Context): FieldServiceDatabase {
        return Room.databaseBuilder(
            context,
            FieldServiceDatabase::class.java,
            FieldServiceDatabase.DATABASE_NAME
        )
            .fallbackToDestructiveMigration()
            .build()
    }

    @Provides
    @Singleton
    fun provideLocationDao(database: FieldServiceDatabase): LocationDao {
        return database.locationDao()
    }

    @Provides
    @Singleton
    fun provideJobLogDao(database: FieldServiceDatabase): JobLogDao {
        return database.jobLogDao()
    }

    @Provides
    @Singleton
    fun provideGson(): Gson {
        return GsonBuilder()
            .setDateFormat("yyyy-MM-dd'T'HH:mm:ss'Z'")
            .setLenient()
            .create()
    }

    @Provides
    @Singleton
    fun provideOkHttpClient(authRepository: AuthRepository): OkHttpClient {
        val loggingInterceptor = HttpLoggingInterceptor().apply {
            level = if (BuildConfig.DEBUG) {
                HttpLoggingInterceptor.Level.BODY
            } else {
                HttpLoggingInterceptor.Level.NONE
            }
        }

        return OkHttpClient.Builder()
            .addInterceptor(loggingInterceptor)
            .addInterceptor { chain ->
                val token = kotlinx.coroutines.runBlocking {
                    authRepository.getAuthToken()
                }
                val request = chain.request().newBuilder()
                    .apply {
                        if (token != null) {
                            addHeader("Authorization", "Bearer $token")
                        }
                        addHeader("Accept", "application/json")
                        addHeader("Content-Type", "application/json")
                    }
                    .build()
                chain.proceed(request)
            }
            .addInterceptor { chain ->
                val request = chain.request()
                val response = chain.proceed(request)
                
                // Handle rate limiting (429)
                if (response.code == 429) {
                    val retryAfter = response.header("Retry-After")?.toIntOrNull() ?: 60
                    Thread.sleep(retryAfter * 1000L)
                    return@addInterceptor chain.proceed(request)
                }
                
                response
            }
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .retryOnConnectionFailure(true)
            .build()
    }

    @Provides
    @Singleton
    fun provideRetrofit(okHttpClient: OkHttpClient, gson: Gson): Retrofit {
        return Retrofit.Builder()
            .baseUrl(BuildConfig.API_BASE_URL)
            .client(okHttpClient)
            .addConverterFactory(GsonConverterFactory.create(gson))
            .build()
    }

    @Provides
    @Singleton
    fun provideApiService(retrofit: Retrofit): ApiService {
        return retrofit.create(ApiService::class.java)
    }

    @Provides
    @Singleton
    fun provideGeofenceHelper(@ApplicationContext context: Context): GeofenceHelper {
        return GeofenceHelper(context)
    }

    @Provides
    @Singleton
    fun provideNetworkConnectivityObserver(@ApplicationContext context: Context): NetworkConnectivityObserver {
        return NetworkConnectivityObserver(context)
    }
}