package com.groupe25.tpexam3.feature.order.presentation.list

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.feature.order.domain.model.Order
import com.groupe25.tpexam3.feature.order.domain.repository.OrderRepository
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch

data class OrderListState(
    val orders: List<Order> = emptyList(),
    val isLoading: Boolean = true
)

class OrderListViewModel(
    private val repository: OrderRepository
) : ViewModel() {

    val state = repository.getAllOrders()
        .map { OrderListState(orders = it, isLoading = false) }
        .stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), OrderListState())

    fun deleteOrder(orderId: Int) {
        viewModelScope.launch {
            repository.deleteOrder(orderId)
        }
    }
}
