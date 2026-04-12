package com.example.carrental.ui

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.carrental.databinding.ActivityMainBinding
import com.example.carrental.api.RetrofitClient
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private lateinit var carAdapter: CarAdapter

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupRecyclerView()
        fetchCars()
    }

    private fun setupRecyclerView() {
        carAdapter = CarAdapter { car ->
            // On Click navigate to detail
        }
        binding.rvCars.apply {
            layoutManager = LinearLayoutManager(this@MainActivity)
            adapter = carAdapter
        }
    }

    private fun fetchCars() {
        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = RetrofitClient.instance.getCars()
                if (response.isSuccessful && response.body()?.status == "success") {
                    withContext(Dispatchers.Main) {
                        carAdapter.submitList(response.body()?.cars ?: emptyList())
                    }
                }
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
    }
}
