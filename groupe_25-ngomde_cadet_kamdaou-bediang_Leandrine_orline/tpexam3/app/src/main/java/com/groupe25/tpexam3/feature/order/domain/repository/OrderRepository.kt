package com.groupe25.tpexam3.feature.order.domain.repository

import com.groupe25.tpexam3.feature.order.domain.model.Order
import com.groupe25.tpexam3.feature.order.domain.model.OrderLine
import kotlinx.coroutines.flow.Flow

interface OrderRepository {
    fun getAllOrders(): Flow<List<Order>>
    fun getOrderLines(orderId: Int): Flow<List<OrderLine>>
    suspend fun createOrder(date: Long, clientId: Int): Long
    suspend fun addProductToOrder(orderId: Int, productId: Int, quantity: Int)
    suspend fun removeProductFromOrder(orderId: Int, productId: Int)
    suspend fun deleteOrder(orderId: Int)
}
