package com.groupe25.tpexam3.feature.dashboard.presentation

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.core.data.local.dao.ClientOrderStat
import com.groupe25.tpexam3.core.data.local.dao.DashboardDao
import com.groupe25.tpexam3.core.data.local.dao.MonthlyStat
import com.groupe25.tpexam3.core.data.local.dao.RegionRevenueStat
import com.groupe25.tpexam3.core.data.local.dao.TopProductStat
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class DashboardState(
    val totalRevenue: Double = 0.0,
    val totalOrders: Int = 0,
    val totalProducts: Int = 0,
    val totalClients: Int = 0,
    val topProducts: List<TopProductStat> = emptyList(),
    val ordersByClient: List<ClientOrderStat> = emptyList(),
    val revenueByRegion: List<RegionRevenueStat> = emptyList(),
    val monthlyStats: List<MonthlyStat> = emptyList(),
    val isLoading: Boolean = true
)

class DashboardViewModel(private val dashboardDao: DashboardDao) : ViewModel() {

    private val _state = MutableStateFlow(DashboardState())
    val state: StateFlow<DashboardState> = _state.asStateFlow()

    init {
        loadDashboard()
    }

    fun loadDashboard() {
        viewModelScope.launch {
            _state.update { it.copy(isLoading = true) }
            _state.update {
                it.copy(
                    totalRevenue = dashboardDao.getTotalRevenue(),
                    totalOrders = dashboardDao.getTotalOrders(),
                    totalProducts = dashboardDao.getTotalProducts(),
                    totalClients = dashboardDao.getTotalClients(),
                    topProducts = dashboardDao.getTopProducts(),
                    ordersByClient = dashboardDao.getOrdersByClient(),
                    revenueByRegion = dashboardDao.getRevenueByRegion(),
                    monthlyStats = dashboardDao.getMonthlyStats(),
                    isLoading = false
                )
            }
        }
    }
}
