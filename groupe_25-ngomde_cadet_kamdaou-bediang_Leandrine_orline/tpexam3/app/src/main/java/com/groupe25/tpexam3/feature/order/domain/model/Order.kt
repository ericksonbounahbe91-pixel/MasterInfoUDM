package com.groupe25.tpexam3.feature.order.domain.model

data class Order(
    val id: Int = 0,
    val date: Long,
    val clientId: Int,
    val clientName: String = "",
    val itemCount: Int = 0,
    val totalAmount: Double = 0.0
)

data class OrderLine(
    val orderId: Int,
    val productId: Int,
    val quantity: Int,
    val productName: String = "",
    val unitPrice: Double = 0.0,
    val photoPath: String? = null
) {
    val lineTotal: Double get() = quantity * unitPrice
}
