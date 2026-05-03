package com.fieldservice.agent.ui.navigation

import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.fieldservice.agent.ui.screens.home.HomeScreen
import com.fieldservice.agent.ui.screens.jobdetails.JobDetailsScreen
import com.fieldservice.agent.ui.screens.login.LoginScreen
import com.fieldservice.agent.ui.screens.login.LoginViewModel

sealed class Screen(val route: String) {
    data object Login : Screen("login")
    data object Home : Screen("home")
    data object JobDetails : Screen("job/{locationId}") {
        fun createRoute(locationId: Int) = "job/$locationId"
    }
    data object Camera : Screen("camera/{locationId}") {
        fun createRoute(locationId: Int) = "camera/$locationId"
    }
    data object Settings : Screen("settings")
}

@Composable
fun AppNavigation(
    navController: NavHostController = rememberNavController(),
    loginViewModel: LoginViewModel = hiltViewModel()
) {
    val isLoggedIn by loginViewModel.isLoggedIn.collectAsState()
    val startDestination = if (isLoggedIn) Screen.Home.route else Screen.Login.route

    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        composable(Screen.Login.route) {
            LoginScreen(
                onLoginSuccess = {
                    navController.navigate(Screen.Home.route) {
                        popUpTo(Screen.Login.route) { inclusive = true }
                    }
                }
            )
        }

        composable(Screen.Home.route) {
            HomeScreen(
                onLocationClick = { locationId ->
                    navController.navigate(Screen.JobDetails.createRoute(locationId))
                },
                onSettingsClick = {
                    navController.navigate(Screen.Settings.route)
                }
            )
        }

        composable(
            route = Screen.JobDetails.route,
            arguments = listOf(navArgument("locationId") { type = NavType.IntType })
        ) { backStackEntry ->
            val locationId = backStackEntry.arguments?.getInt("locationId") ?: 0
            JobDetailsScreen(
                locationId = locationId,
                onBack = { navController.popBackStack() },
                onTakePhoto = {
                    navController.navigate(Screen.Camera.createRoute(locationId))
                }
            )
        }

        composable(
            route = Screen.Camera.route,
            arguments = listOf(navArgument("locationId") { type = NavType.IntType })
        ) { backStackEntry ->
            val locationId = backStackEntry.arguments?.getInt("locationId") ?: 0
            CameraScreen(
                locationId = locationId,
                onBack = { navController.popBackStack() },
                onPhotoTaken = { navController.popBackStack() }
            )
        }

        composable(Screen.Settings.route) {
            SettingsScreen(
                onBack = { navController.popBackStack() },
                onLogout = {
                    loginViewModel.logout()
                    navController.navigate(Screen.Login.route) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }
    }
}
