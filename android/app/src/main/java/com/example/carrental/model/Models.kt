package com.example.carrental.model

data class Car(
    val id: Int,
    val brand: String,
    val model: String,
    val type: String?,
    val fuel_type: String?,
    val transmission: String?,
    val daily_rate: Double,
    val seating_capacity: Int,
    val image: String?,
    val availability_status: Int
)

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val role: String
)

data class CarListResponse(
    val status: String,
    val cars: List<Car>
)

data class LoginResponse(
    val status: String,
    val user: User?,
    val message: String?
)

data class LoginRequest(
    val email: String,
    val password: String
)

data class BaseResponse(
    val status: String,
    val message: String
)

data class BookingRequest(
    val user_id: Int,
    val car_id: Int,
    val start_date: String,
    val end_date: String
)

data class Booking(
    val id: Int,
    val user_id: Int,
    val car_id: Int,
    val brand: String,
    val model: String,
    val start_date: String,
    val end_date: String,
    val total_price: Double,
    val status: String
)

data class BookingListResponse(
    val status: String,
    val bookings: List<Booking>
)
