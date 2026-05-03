package com.fieldservice.agent.ui.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.navArgument
import com.fieldservice.agent.ui.screens.camera.CameraScreen
import com.fieldservice.agent.ui.screens.home.HomeScreen
import com.fieldservice.agent.ui.screens.jobdetails.JobDetailsScreen
import com.fieldservice.agent.ui.screens.login.LoginScreen
import com.fieldservice.agent.ui.screens.notifications.NotificationsScreen
import com.fieldservice.agent.ui.screens.settings.SettingsScreen

sealed class Screen(val route: String) {
    data object Login : Screen("login")
    data object Home : Screen("home")
    data object JobDetails : Screen("job_details/{locationId}") {
        fun createRoute(locationId: Int) = "job_details/$locationId"
    }
    data object Camera : Screen("camera/{locationId}") {
        fun createRoute(locationId: Int) = "camera/$locationId"
    }
    data object Settings : Screen("settings")
    data object Notifications : Screen("notifications")
}

@Composable
fun AppNavigation(
    navController: NavHostController,
    startDestination: String
) {
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
                },
                onNotificationClick = {
                    navController.navigate(Screen.Notifications.route)
                }
            )
        }

        composable(
            route = Screen.JobDetails.route,
            arguments = listOf(
                navArgument("locationId") { type = NavType.IntType }
            )
        ) { backStackEntry ->
            val locationId = backStackEntry.arguments?.getInt("locationId") ?: return@composable
            JobDetailsScreen(
                locationId = locationId,
                onNavigateBack = { navController.popBackStack() },
                onOpenCamera = { 
                    navController.navigate(Screen.Camera.createRoute(locationId))
                }
            )
        }

        composable(
            route = Screen.Camera.route,
            arguments = listOf(
                navArgument("locationId") { type = NavType.IntType }
            )
        ) { backStackEntry ->
            val locationId = backStackEntry.arguments?.getInt("locationId") ?: return@composable
            CameraScreen(
                locationId = locationId,
                onNavigateBack = { navController.popBackStack() }
            )
        }

        composable(Screen.Settings.route) {
            SettingsScreen(
                onNavigateBack = { navController.popBackStack() },
                onLogout = {
                    navController.navigate(Screen.Login.route) {
                        popUpTo(0) { inclusive = true }
                    }
                }
            )
        }

        composable(Screen.Notifications.route) {
            NotificationsScreen(
                onNavigateBack = { navController.popBackStack() }
            )
        }
    }
}