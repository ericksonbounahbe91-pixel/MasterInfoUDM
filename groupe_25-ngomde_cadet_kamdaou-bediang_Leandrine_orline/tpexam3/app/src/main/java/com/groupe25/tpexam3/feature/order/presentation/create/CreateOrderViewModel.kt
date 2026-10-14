package com.groupe25.tpexam3.feature.order.presentation.create

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.core.data.local.dao.ClientDao
import com.groupe25.tpexam3.core.data.local.dao.ClientWithRegion
import com.groupe25.tpexam3.feature.order.domain.repository.OrderRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class CreateOrderState(
    val clients: List<ClientWithRegion> = emptyList(),
    val selectedClientId: Int? = null,
    val isLoading: Boolean = true,
    val createdOrderId: Long? = null
)

class CreateOrderViewModel(
    private val orderRepository: OrderRepository,
    clientDao: ClientDao
) : ViewModel() {

    private val _uiState = MutableStateFlow(CreateOrderState())

    val state = combine(
        _uiState,
        clientDao.getAllClientsWithRegion()
    ) { ui, clients ->
        ui.copy(clients = clients, isLoading = false)
    }.stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), _uiState.value)

    fun onClientSelected(clientId: Int) {
        _uiState.update { it.copy(selectedClientId = clientId) }
    }

    fun createOrder() {
        val clientId = _uiState.value.selectedClientId ?: return
        viewModelScope.launch {
            val orderId = orderRepository.createOrder(
                date = System.currentTimeMillis(),
                clientId = clientId
            )
            _uiState.update { it.copy(createdOrderId = orderId) }
        }
    }
}
