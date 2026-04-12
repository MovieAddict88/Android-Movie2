package com.example.carrental.ui

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.RecyclerView
import com.example.carrental.databinding.ItemCarBinding
import com.example.carrental.model.Car
import com.bumptech.glide.Glide

class CarAdapter(private val onClick: (Car) -> Unit) : RecyclerView.Adapter<CarAdapter.CarViewHolder>() {
    private var cars = listOf<Car>()

    fun submitList(newCars: List<Car>) {
        cars = newCars
        notifyDataSetChanged()
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): CarViewHolder {
        val binding = ItemCarBinding.inflate(LayoutInflater.from(parent.context), parent, false)
        return CarViewHolder(binding)
    }

    override fun onBindViewHolder(holder: CarViewHolder, position: Int) {
        val car = cars[position]
        holder.bind(car)
    }

    override fun getItemCount() = cars.size

    inner class CarViewHolder(private val binding: ItemCarBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(car: Car) {
            binding.tvBrand.text = "${car.brand} ${car.model}"
            binding.tvPrice.text = "$${car.daily_rate}/day"
            
            Glide.with(binding.ivCar.context)
                .load(car.image)
                .placeholder(android.R.drawable.ic_menu_gallery)
                .into(binding.ivCar)

            binding.root.setOnClickListener { onClick(car) }
        }
    }
}
