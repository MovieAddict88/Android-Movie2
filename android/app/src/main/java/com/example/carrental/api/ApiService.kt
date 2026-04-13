package com.example.carrental.api

import com.example.carrental.model.*
import okhttp3.MultipartBody
import okhttp3.RequestBody
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

    @Multipart
    @POST("api/submit_payment.php")
    suspend fun submitPayment(
        @Part("booking_id") bookingId: RequestBody,
        @Part("payment_method") paymentMethod: RequestBody,
        @Part("reference_number") referenceNumber: RequestBody,
        @Part("amount") amount: RequestBody,
        @Part proofOfPayment: MultipartBody.Part?
    ): Response<BaseResponse>

    @GET("api/admin/get_all_payments.php")
    suspend fun getAllPayments(): Response<PaymentListResponse>

    @POST("api/admin/update_payment_status.php")
    suspend fun updatePaymentStatus(@Body request: UpdatePaymentStatusRequest): Response<BaseResponse>

    @POST("api/update_location.php")
    suspend fun updateLocation(@Body request: LocationRequest): Response<BaseResponse>
}
