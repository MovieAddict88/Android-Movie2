package com.example.carrental.api

import com.example.carrental.model.*
import retrofit2.Response
import retrofit2.http.*

interface ApiService {
    @POST("api/login.php")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @GET("api/get_cars.php")
    suspend fun getCars(): Response<CarListResponse>

    @POST("api/create_booking.php")
    suspend fun createBooking(@Body request: BookingRequest): Response<BaseResponse>

    @GET("api/get_my_bookings.php")
    suspend fun getMyBookings(@Query("user_id") userId: Int): Response<BookingListResponse>
}
