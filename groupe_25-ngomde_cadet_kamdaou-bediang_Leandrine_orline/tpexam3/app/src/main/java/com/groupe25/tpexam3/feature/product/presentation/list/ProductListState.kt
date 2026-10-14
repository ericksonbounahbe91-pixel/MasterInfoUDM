package com.groupe25.tpexam3.feature.product.presentation.list

import com.groupe25.tpexam3.feature.product.domain.model.Product

data class ProductListState(
    val products: List<Product> = emptyList(),
    val searchQuery: String = "",
    val isLoading: Boolean = true
)
