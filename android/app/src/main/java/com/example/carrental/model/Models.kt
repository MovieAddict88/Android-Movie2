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

data class Payment(
    val id: Int,
    val booking_id: Int,
    val payment_method: String,
    val reference_number: String,
    val amount: Double,
    val proof_of_payment: String?,
    val status: String,
    val rejection_reason: String?,
    val user_name: String,
    val created_at: String
)

data class PaymentListResponse(
    val status: String,
    val payments: List<Payment>
)

data class UpdatePaymentStatusRequest(
    val payment_id: Int,
    val action: String,
    val rejection_reason: String? = null
)
