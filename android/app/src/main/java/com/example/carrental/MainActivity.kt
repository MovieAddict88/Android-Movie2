package com.example.carrental

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Scaffold
import androidx.compose.ui.Modifier
import com.example.carrental.ui.screens.CarListScreen
import com.example.carrental.ui.screens.LoginScreen
import com.example.carrental.ui.screens.PaymentScreen
import com.example.carrental.ui.screens.AdminPaymentScreen
import com.example.carrental.ui.screens.MapScreen
import com.example.carrental.ui.theme.CarRentalTheme
import com.example.carrental.viewmodel.CarViewModel
import com.example.carrental.viewmodel.PaymentViewModel
import androidx.lifecycle.viewmodel.compose.viewModel

import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.NavType
import androidx.navigation.navArgument

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            CarRentalTheme {
                val navController = rememberNavController()
                Scaffold(modifier = Modifier.fillMaxSize()) { innerPadding ->
                    NavHost(
                        navController = navController,
                        startDestination = "login",
                        modifier = Modifier.padding(innerPadding)
                    ) {
                        composable("login") {
                            LoginScreen(onLoginSuccess = {
                                navController.navigate("car_list") {
                                    popUpTo("login") { inclusive = true }
                                }
                            })
                        }
                        composable("car_list") {
                            val viewModel: CarViewModel = viewModel()
                            CarListScreen(
                                viewModel = viewModel,
                                onAdminClick = { navController.navigate("admin_payments") },
                                onMapClick = { navController.navigate("map") },
                                onBookClick = { bookingId, amount -> 
                                    navController.navigate("payment/$bookingId/$amount")
                                }
                            )
                        }
                        composable("map") {
                            MapScreen(onBackClick = { navController.popBackStack() })
                        }
                        composable(
                            "payment/{bookingId}/{amount}",
                            arguments = listOf(
                                navArgument("bookingId") { type = NavType.IntType },
                                navArgument("amount") { type = NavType.FloatType }
                            )
                        ) { backStackEntry ->
                            val bookingId = backStackEntry.arguments?.getInt("bookingId") ?: 0
                            val amount = backStackEntry.arguments?.getFloat("amount")?.toDouble() ?: 0.0
                            val viewModel: PaymentViewModel = viewModel()
                            PaymentScreen(bookingId, amount, viewModel) {
                                navController.popBackStack()
                            }
                        }
                        composable("admin_payments") {
                            val viewModel: PaymentViewModel = viewModel()
                            AdminPaymentScreen(viewModel)
                        }
                    }
                }
            }
        }
    }
}
