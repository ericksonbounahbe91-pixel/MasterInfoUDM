package com.groupe25.tpexam3.core.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.groupe25.tpexam3.core.data.local.entity.CommandeEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface CommandeDao {
    @Query("""
        SELECT cmd.idCmd, cmd.dateCmd, cmd.idCli,
               c.nomCli, c.prenomCli,
               COUNT(lc.idProd) AS itemCount,
               COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS totalAmount
        FROM commande cmd
        LEFT JOIN client c ON cmd.idCli = c.idCli
        LEFT JOIN ligne_commande lc ON cmd.idCmd = lc.idCmd
        LEFT JOIN produit p ON lc.idProd = p.idProd
        GROUP BY cmd.idCmd
        ORDER BY cmd.dateCmd DESC
    """)
    fun getAllOrdersWithDetails(): Flow<List<OrderWithDetails>>

    @Query("SELECT * FROM commande WHERE idCmd = :id")
    suspend fun getOrderById(id: Int): CommandeEntity?

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(commande: CommandeEntity): Long

    @Query("DELETE FROM commande WHERE idCmd = :id")
    suspend fun deleteById(id: Int)
}

data class OrderWithDetails(
    val idCmd: Int,
    val dateCmd: Long,
    val idCli: Int,
    val nomCli: String?,
    val prenomCli: String?,
    val itemCount: Int,
    val totalAmount: Double
)
