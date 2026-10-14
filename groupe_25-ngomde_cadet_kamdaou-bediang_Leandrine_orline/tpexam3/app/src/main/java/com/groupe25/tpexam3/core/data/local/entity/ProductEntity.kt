package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "produit")
data class ProductEntity(
    @PrimaryKey(autoGenerate = true) val idProd: Int = 0,
    val designProd: String,
    val qteStock: Int,
    val prixUProd: Double,
    val photoProd: String? = null
)
