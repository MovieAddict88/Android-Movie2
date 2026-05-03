package com.fieldservice.agent.data.repository

import android.content.Context
import android.util.Log
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.*
import androidx.datastore.preferences.preferencesDataStore
import com.fieldservice.agent.data.remote.api.*
import com.google.gson.Gson
import dagger.hilt.android.qualifiers.ApplicationContext
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map
import javax.inject.Inject
import javax.inject.Singleton

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = "auth_prefs")

@Singleton
class AuthRepository @Inject constructor(
    @ApplicationContext private val context: Context,
    private val apiService: ApiService,
    private val gson: Gson
) {
    private val dataStore = context.dataStore

    private object Keys {
        val AUTH_TOKEN = stringPreferencesKey("auth_token")
        val USER_ID = intPreferencesKey("user_id")
        val USER_NAME = stringPreferencesKey("user_name")
        val USER_EMAIL = stringPreferencesKey("user_email")
        val USER_PHONE = stringPreferencesKey("user_phone")
        val COMPANY_ID = intPreferencesKey("company_id")
        val COMPANY_NAME = stringPreferencesKey("company_name")
        val USER_ROLE = stringPreferencesKey("user_role")
        val DEVICE_TOKEN = stringPreferencesKey("device_token")
        val TOKEN_EXPIRY = longPreferencesKey("token_expiry")
    }

    val isLoggedIn: Flow<Boolean> = dataStore.data.map { prefs ->
        prefs[Keys.AUTH_TOKEN] != null
    }

    val currentUser: Flow<UserDto?> = dataStore.data.map { prefs ->
        val id = prefs[Keys.USER_ID] ?: return@map null
        UserDto(
            id = id,
            name = prefs[Keys.USER_NAME] ?: "",
            email = prefs[Keys.USER_EMAIL] ?: "",
            phone = prefs[Keys.USER_PHONE],
            companyId = prefs[Keys.COMPANY_ID] ?: 0,
            companyName = prefs[Keys.COMPANY_NAME] ?: "",
            role = prefs[Keys.USER_ROLE] ?: "",
            lastActiveAt = null
        )
    }

    suspend fun getAuthToken(): String? {
        return dataStore.data.first()[Keys.AUTH_TOKEN]
    }

    suspend fun saveAuthToken(token: String) {
        dataStore.edit { prefs ->
            prefs[Keys.AUTH_TOKEN] = token
        }
    }

    suspend fun saveUser(user: UserDto) {
        dataStore.edit { prefs ->
            prefs[Keys.USER_ID] = user.id
            prefs[Keys.USER_NAME] = user.name
            prefs[Keys.USER_EMAIL] = user.email
            prefs[Keys.USER_PHONE] = user.phone ?: ""
            prefs[Keys.COMPANY_ID] = user.companyId
            prefs[Keys.COMPANY_NAME] = user.companyName ?: ""
            prefs[Keys.USER_ROLE] = user.role
        }
    }

    suspend fun saveUser(user: UserData) {
        dataStore.edit { prefs ->
            prefs[Keys.USER_ID] = user.id
            prefs[Keys.USER_NAME] = user.name
            prefs[Keys.USER_EMAIL] = user.email
            prefs[Keys.USER_PHONE] = user.phone ?: ""
            prefs[Keys.COMPANY_ID] = user.companyId
            prefs[Keys.COMPANY_NAME] = user.companyName ?: ""
            prefs[Keys.USER_ROLE] = user.role
        }
    }

    suspend fun saveDeviceToken(token: String) {
        dataStore.edit { prefs ->
            prefs[Keys.DEVICE_TOKEN] = token
        }
    }

    suspend fun getSavedDeviceToken(): String? {
        return dataStore.data.first()[Keys.DEVICE_TOKEN]
    }

    suspend fun logout(): Boolean {
        return try {
            val response = apiService.logout()
            clearAuth()
            true
        } catch (e: Exception) {
            Log.e(TAG, "Logout API call failed", e)
            // Still clear local auth even if API fails
            clearAuth()
            true
        }
    }

    suspend fun refreshUser(): Result<UserDto> {
        return try {
            val response = apiService.getCurrentUser()
            if (response.success && response.data != null) {
                saveUser(response.data)
                Result.success(response.data)
            } else {
                Result.failure(Exception(response.message ?: "Failed to refresh user"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun clearAuth() {
        dataStore.edit { prefs ->
            prefs.remove(Keys.AUTH_TOKEN)
            prefs.remove(Keys.USER_ID)
            prefs.remove(Keys.USER_NAME)
            prefs.remove(Keys.USER_EMAIL)
            prefs.remove(Keys.USER_PHONE)
            prefs.remove(Keys.COMPANY_ID)
            prefs.remove(Keys.COMPANY_NAME)
            prefs.remove(Keys.USER_ROLE)
            prefs.remove(Keys.TOKEN_EXPIRY)
        }
    }

    suspend fun isTokenExpired(): Boolean {
        val expiry = dataStore.data.first()[Keys.TOKEN_EXPIRY] ?: return false
        return System.currentTimeMillis() > expiry
    }

    companion object {
        private const val TAG = "AuthRepository"
    }
}