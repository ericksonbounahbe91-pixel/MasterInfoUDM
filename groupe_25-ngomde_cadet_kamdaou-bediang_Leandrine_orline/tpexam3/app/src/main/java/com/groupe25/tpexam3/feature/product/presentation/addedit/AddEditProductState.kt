package com.groupe25.tpexam3.feature.product.presentation.addedit

import com.groupe25.tpexam3.core.presentation.UiText

data class AddEditProductState(
    val designation: String = "",
    val stockQuantity: String = "",
    val unitPrice: String = "",
    val photoPath: String? = null,
    val isEditing: Boolean = false,
    val isLoading: Boolean = false,
    val isSaved: Boolean = false,
    val designationError: UiText? = null,
    val stockError: UiText? = null,
    val priceError: UiText? = null
)
