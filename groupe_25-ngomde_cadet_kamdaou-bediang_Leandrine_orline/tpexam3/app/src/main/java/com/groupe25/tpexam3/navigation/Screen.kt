package com.groupe25.tpexam3.navigation

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Dashboard
import androidx.compose.material.icons.filled.Inventory
import androidx.compose.material.icons.filled.People
import androidx.compose.material.icons.filled.ShoppingCart
import androidx.compose.ui.graphics.vector.ImageVector
import com.groupe25.tpexam3.R

sealed class Screen(val route: String) {
    data object Login : Screen("login")
    data object Products : Screen("products")
    data object AddEditProduct : Screen("add_edit_product?productId={productId}") {
        fun createRoute(productId: Int? = null): String {
            return if (productId != null) "add_edit_product?productId=$productId"
            else "add_edit_product"
        }
    }
    data object Orders : Screen("orders")
    data object Clients : Screen("clients")
    data object Dashboard : Screen("dashboard")
}

enum class BottomNavItem(
    val route: String,
    val icon: ImageVector,
    val labelResId: Int
) {
    Products(Screen.Products.route, Icons.Default.Inventory, R.string.nav_products),
    Orders(Screen.Orders.route, Icons.Default.ShoppingCart, R.string.nav_orders),
    Clients(Screen.Clients.route, Icons.Default.People, R.string.nav_clients),
    Dashboard(Screen.Dashboard.route, Icons.Default.Dashboard, R.string.nav_dashboard)
}
