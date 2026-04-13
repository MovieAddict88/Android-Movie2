package com.example.carrental.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.carrental.api.RetrofitClient
import com.example.carrental.model.AppSettings
import com.example.carrental.model.Car
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class CarViewModel : ViewModel() {
    private val _cars = MutableStateFlow<List<Car>>(emptyList())
    val cars: StateFlow<List<Car>> = _cars

    private val _settings = MutableStateFlow<AppSettings?>(null)
    val settings: StateFlow<AppSettings?> = _settings

    private val _loading = MutableStateFlow(false)
    val loading: StateFlow<Boolean> = _loading

    init {
        fetchCars()
        fetchSettings()
    }

    fun fetchCars() {
        viewModelScope.launch {
            _loading.value = true
            try {
                val response = RetrofitClient.instance.getCars()
                if (response.isSuccessful && response.body()?.status == "success") {
                    _cars.value = response.body()?.cars ?: emptyList()
                }
            } catch (e: Exception) {
                e.printStackTrace()
            } finally {
                _loading.value = false
            }
        }
    }

    fun fetchSettings() {
        viewModelScope.launch {
            try {
                val response = RetrofitClient.instance.getSettings()
                if (response.isSuccessful && response.body()?.status == "success") {
                    _settings.value = response.body()?.settings
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }
}
