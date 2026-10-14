package com.groupe25.tpexam3.feature.product.domain.model

data class Product(
    val id: Int = 0,
    val designation: String,
    val stockQuantity: Int,
    val unitPrice: Double,
    val photoPath: String? = null
)
