package com.groupe25.tpexam3.feature.product.presentation.addedit

import android.content.Context
import android.net.Uri
import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.R
import com.groupe25.tpexam3.core.presentation.UiText
import com.groupe25.tpexam3.feature.product.domain.model.Product
import com.groupe25.tpexam3.feature.product.domain.repository.ProductRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch
import java.io.File
import java.util.UUID

class AddEditProductViewModel(
    private val repository: ProductRepository,
    savedStateHandle: SavedStateHandle
) : ViewModel() {

    private val productId: Int? = savedStateHandle.get<Int>("productId")?.takeIf { it != -1 }

    private val _state = MutableStateFlow(AddEditProductState())
    val state: StateFlow<AddEditProductState> = _state.asStateFlow()

    init {
        productId?.let { id ->
            viewModelScope.launch {
                repository.getProductById(id)?.let { product ->
                    _state.update {
                        it.copy(
                            designation = product.designation,
                            stockQuantity = product.stockQuantity.toString(),
                            unitPrice = product.unitPrice.toString(),
                            photoPath = product.photoPath,
                            isEditing = true
                        )
                    }
                }
            }
        }
    }

    fun onDesignationChange(value: String) {
        _state.update { it.copy(designation = value, designationError = null) }
    }

    fun onStockChange(value: String) {
        _state.update { it.copy(stockQuantity = value, stockError = null) }
    }

    fun onPriceChange(value: String) {
        _state.update { it.copy(unitPrice = value, priceError = null) }
    }

    fun onPhotoSelected(uri: Uri, context: Context) {
        viewModelScope.launch {
            val file = File(context.filesDir, "products/${UUID.randomUUID()}.jpg")
            file.parentFile?.mkdirs()
            context.contentResolver.openInputStream(uri)?.use { input ->
                file.outputStream().use { output ->
                    input.copyTo(output)
                }
            }
            _state.update { it.copy(photoPath = file.absolutePath) }
        }
    }

    fun onSave() {
        val current = _state.value
        var hasError = false

        if (current.designation.isBlank()) {
            _state.update { it.copy(designationError = UiText.StringResource(R.string.field_required)) }
            hasError = true
        }

        val stock = current.stockQuantity.toIntOrNull()
        if (stock == null) {
            _state.update { it.copy(stockError = UiText.StringResource(R.string.invalid_number)) }
            hasError = true
        }

        val price = current.unitPrice.toDoubleOrNull()
        if (price == null) {
            _state.update { it.copy(priceError = UiText.StringResource(R.string.invalid_number)) }
            hasError = true
        }

        if (hasError) return

        viewModelScope.launch {
            _state.update { it.copy(isLoading = true) }
            val product = Product(
                id = productId ?: 0,
                designation = current.designation.trim(),
                stockQuantity = stock!!,
                unitPrice = price!!,
                photoPath = current.photoPath
            )
            if (_state.value.isEditing) {
                repository.updateProduct(product)
            } else {
                repository.insertProduct(product)
            }
            _state.update { it.copy(isLoading = false, isSaved = true) }
        }
    }
}
