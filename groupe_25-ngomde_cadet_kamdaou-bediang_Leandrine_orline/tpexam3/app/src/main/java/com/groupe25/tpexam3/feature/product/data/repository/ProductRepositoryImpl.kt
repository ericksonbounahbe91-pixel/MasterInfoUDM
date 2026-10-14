package com.groupe25.tpexam3.feature.product.data.repository

import com.groupe25.tpexam3.core.data.local.dao.ProductDao
import com.groupe25.tpexam3.core.data.local.entity.ProductEntity
import com.groupe25.tpexam3.feature.product.domain.model.Product
import com.groupe25.tpexam3.feature.product.domain.repository.ProductRepository
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

class ProductRepositoryImpl(private val productDao: ProductDao) : ProductRepository {

    override fun getAllProducts(): Flow<List<Product>> {
        return productDao.getAllProducts().map { entities ->
            entities.map { it.toDomain() }
        }
    }

    override fun searchProducts(query: String): Flow<List<Product>> {
        return productDao.searchProducts(query).map { entities ->
            entities.map { it.toDomain() }
        }
    }

    override suspend fun getProductById(id: Int): Product? {
        return productDao.getProductById(id)?.toDomain()
    }

    override suspend fun insertProduct(product: Product): Long {
        return productDao.insert(product.toEntity())
    }

    override suspend fun updateProduct(product: Product) {
        productDao.update(product.toEntity())
    }

    override suspend fun deleteProduct(product: Product) {
        productDao.delete(product.toEntity())
    }

    private fun ProductEntity.toDomain() = Product(
        id = idProd,
        designation = designProd,
        stockQuantity = qteStock,
        unitPrice = prixUProd,
        photoPath = photoProd
    )

    private fun Product.toEntity() = ProductEntity(
        idProd = id,
        designProd = designation,
        qteStock = stockQuantity,
        prixUProd = unitPrice,
        photoProd = photoPath
    )
}
