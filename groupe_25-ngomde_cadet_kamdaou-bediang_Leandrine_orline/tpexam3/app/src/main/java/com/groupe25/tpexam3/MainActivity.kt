package com.groupe25.tpexam3

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Icon
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.res.stringResource
import androidx.navigation.NavHostController
import androidx.navigation.NavType
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import androidx.navigation.navArgument
import com.groupe25.tpexam3.feature.auth.presentation.LoginScreen
import com.groupe25.tpexam3.feature.client.presentation.ClientListScreen
import com.groupe25.tpexam3.feature.dashboard.presentation.DashboardScreen
import com.groupe25.tpexam3.feature.order.presentation.create.CreateOrderScreen
import com.groupe25.tpexam3.feature.order.presentation.detail.OrderDetailScreen
import com.groupe25.tpexam3.feature.order.presentation.list.OrderListScreen
import com.groupe25.tpexam3.feature.product.presentation.addedit.AddEditProductScreen
import com.groupe25.tpexam3.feature.product.presentation.list.ProductListScreen
import com.groupe25.tpexam3.navigation.BottomNavItem
import com.groupe25.tpexam3.navigation.Screen
import com.groupe25.tpexam3.ui.theme.TpExam3Theme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            TpExam3Theme {
                MainApp()
            }
        }
    }
}

@Composable
fun MainApp() {
    val navController = rememberNavController()
    val navBackStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = navBackStackEntry?.destination?.route

    val showBottomBar = BottomNavItem.entries.any { currentRoute == it.route }

    Scaffold(
        bottomBar = {
            if (showBottomBar) {
                BottomNavigationBar(navController = navController, currentRoute = currentRoute)
            }
        }
    ) { paddingValues ->
        NavHost(
            navController = navController,
            startDestination = Screen.Login.route,
            modifier = Modifier.padding(paddingValues)
        ) {
            // Auth
            composable(Screen.Login.route) {
                LoginScreen(
                    onLoginSuccess = {
                        navController.navigate(Screen.Products.route) {
                            popUpTo(Screen.Login.route) { inclusive = true }
                        }
                    }
                )
            }

            // Products
            composable(Screen.Products.route) {
                ProductListScreen(
                    onAddProduct = {
                        navController.navigate(Screen.AddEditProduct.createRoute())
                    },
                    onEditProduct = { productId ->
                        navController.navigate(Screen.AddEditProduct.createRoute(productId))
                    }
                )
            }

            composable(
                route = Screen.AddEditProduct.route,
                arguments = listOf(
                    navArgument("productId") {
                        type = NavType.IntType
                        defaultValue = -1
                    }
                )
            ) {
                AddEditProductScreen(onBack = { navController.popBackStack() })
            }

            // Orders
            composable(Screen.Orders.route) {
                OrderListScreen(
                    onOrderClick = { orderId ->
                        navController.navigate(Screen.OrderDetail.createRoute(orderId))
                    },
                    onAddOrder = {
                        navController.navigate(Screen.CreateOrder.route)
                    }
                )
            }

            composable(Screen.CreateOrder.route) {
                CreateOrderScreen(
                    onBack = { navController.popBackStack() },
                    onOrderCreated = { orderId ->
                        navController.popBackStack()
                        navController.navigate(Screen.OrderDetail.createRoute(orderId))
                    }
                )
            }

            composable(
                route = Screen.OrderDetail.route,
                arguments = listOf(
                    navArgument("orderId") { type = NavType.IntType }
                )
            ) {
                OrderDetailScreen(onBack = { navController.popBackStack() })
            }

            // Clients
            composable(Screen.Clients.route) {
                ClientListScreen()
            }

            // Dashboard
            composable(Screen.Dashboard.route) {
                DashboardScreen(
                    onLogout = {
                        navController.navigate(Screen.Login.route) {
                            popUpTo(0) { inclusive = true }
                        }
                    }
                )
            }
        }
    }
}

@Composable
private fun BottomNavigationBar(navController: NavHostController, currentRoute: String?) {
    NavigationBar {
        BottomNavItem.entries.forEach { item ->
            NavigationBarItem(
                icon = { Icon(item.icon, contentDescription = null) },
                label = { Text(stringResource(item.labelResId)) },
                selected = currentRoute == item.route,
                onClick = {
                    if (currentRoute != item.route) {
                        navController.navigate(item.route) {
                            popUpTo(Screen.Products.route) { saveState = true }
                            launchSingleTop = true
                            restoreState = true
                        }
                    }
                }
            )
        }
    }
}
