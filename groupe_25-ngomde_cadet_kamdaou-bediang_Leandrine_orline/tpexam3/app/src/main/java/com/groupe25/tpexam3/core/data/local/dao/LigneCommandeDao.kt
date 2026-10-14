package com.groupe25.tpexam3.core.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.groupe25.tpexam3.core.data.local.entity.LigneCommandeEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface LigneCommandeDao {
    @Query("""
        SELECT lc.idCmd, lc.idProd, lc.qteCmd,
               p.designProd, p.prixUProd, p.photoProd
        FROM ligne_commande lc
        JOIN produit p ON lc.idProd = p.idProd
        WHERE lc.idCmd = :orderId
    """)
    fun getOrderLinesByOrderId(orderId: Int): Flow<List<OrderLineWithProduct>>

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(ligneCommande: LigneCommandeEntity)

    @Query("DELETE FROM ligne_commande WHERE idCmd = :orderId AND idProd = :productId")
    suspend fun delete(orderId: Int, productId: Int)
}

data class OrderLineWithProduct(
    val idCmd: Int,
    val idProd: Int,
    val qteCmd: Int,
    val designProd: String,
    val prixUProd: Double,
    val photoProd: String?
)
