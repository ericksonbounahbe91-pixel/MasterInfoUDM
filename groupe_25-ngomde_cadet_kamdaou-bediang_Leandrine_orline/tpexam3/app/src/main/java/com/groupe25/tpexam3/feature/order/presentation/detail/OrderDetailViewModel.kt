package com.groupe25.tpexam3.feature.order.presentation.detail

import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.feature.order.domain.model.OrderLine
import com.groupe25.tpexam3.feature.order.domain.repository.OrderRepository
import com.groupe25.tpexam3.feature.product.domain.model.Product
import com.groupe25.tpexam3.feature.product.domain.repository.ProductRepository
import kotlinx.coroutines.ExperimentalCoroutinesApi
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.combine
import kotlinx.coroutines.flow.flatMapLatest
import kotlinx.coroutines.flow.flowOf
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

data class OrderDetailState(
    val orderId: Int = -1,
    val orderLines: List<OrderLine> = emptyList(),
    val products: List<Product> = emptyList(),
    val showAddDialog: Boolean = false,
    val selectedProductId: Int? = null,
    val quantity: String = "1",
    val isLoading: Boolean = true
)

@OptIn(ExperimentalCoroutinesApi::class)
class OrderDetailViewModel(
    private val orderRepository: OrderRepository,
    private val productRepository: ProductRepository,
    savedStateHandle: SavedStateHandle
) : ViewModel() {

    private val orderId: Int = savedStateHandle.get<Int>("orderId") ?: -1

    private val _dialogState = MutableStateFlow(
        OrderDetailState(orderId = orderId)
    )

    private val orderLines = if (orderId != -1) {
        orderRepository.getOrderLines(orderId)
    } else {
        flowOf(emptyList())
    }

    val state: StateFlow<OrderDetailState> = combine(
        _dialogState,
        orderLines,
        productRepository.getAllProducts()
    ) { dialog, lines, products ->
        dialog.copy(
            orderLines = lines,
            products = products,
            isLoading = false
        )
    }.stateIn(viewModelScope, SharingStarted.WhileSubscribed(5000), _dialogState.value)

    fun showAddDialog() {
        _dialogState.update { it.copy(showAddDialog = true, selectedProductId = null, quantity = "1") }
    }

    fun hideAddDialog() {
        _dialogState.update { it.copy(showAddDialog = false) }
    }

    fun onProductSelected(productId: Int) {
        _dialogState.update { it.copy(selectedProductId = productId) }
    }

    fun onQuantityChange(qty: String) {
        _dialogState.update { it.copy(quantity = qty) }
    }

    fun addProductToOrder() {
        val current = _dialogState.value
        val productId = current.selectedProductId ?: return
        val qty = current.quantity.toIntOrNull() ?: return

        viewModelScope.launch {
            orderRepository.addProductToOrder(orderId, productId, qty)
            _dialogState.update { it.copy(showAddDialog = false) }
        }
    }

    fun removeProductFromOrder(productId: Int) {
        viewModelScope.launch {
            orderRepository.removeProductFromOrder(orderId, productId)
        }
    }
}
