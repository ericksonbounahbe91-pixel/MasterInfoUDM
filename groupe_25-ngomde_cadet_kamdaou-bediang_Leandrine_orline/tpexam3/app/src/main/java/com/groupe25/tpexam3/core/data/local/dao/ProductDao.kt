package com.groupe25.tpexam3.core.data.local.dao

import androidx.room.Dao
import androidx.room.Delete
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import androidx.room.Update
import com.groupe25.tpexam3.core.data.local.entity.ProductEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface ProductDao {
    @Query("SELECT * FROM produit ORDER BY designProd ASC")
    fun getAllProducts(): Flow<List<ProductEntity>>

    @Query("SELECT * FROM produit WHERE idProd = :id")
    suspend fun getProductById(id: Int): ProductEntity?

    @Query("SELECT * FROM produit WHERE designProd LIKE '%' || :query || '%'")
    fun searchProducts(query: String): Flow<List<ProductEntity>>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(product: ProductEntity): Long

    @Update
    suspend fun update(product: ProductEntity)

    @Delete
    suspend fun delete(product: ProductEntity)

    @Query("SELECT COUNT(*) FROM produit")
    suspend fun getProductCount(): Int
}
