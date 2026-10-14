package com.groupe25.tpexam3.core.data.local.dao

import androidx.room.Dao
import androidx.room.Query

@Dao
interface DashboardDao {
    @Query("SELECT COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) FROM ligne_commande lc JOIN produit p ON lc.idProd = p.idProd")
    suspend fun getTotalRevenue(): Double

    @Query("SELECT COUNT(*) FROM commande")
    suspend fun getTotalOrders(): Int

    @Query("SELECT COUNT(*) FROM produit")
    suspend fun getTotalProducts(): Int

    @Query("SELECT COUNT(*) FROM client")
    suspend fun getTotalClients(): Int

    @Query("""
        SELECT p.designProd, SUM(lc.qteCmd) AS totalQty, SUM(lc.qteCmd * p.prixUProd) AS totalRevenue
        FROM ligne_commande lc
        JOIN produit p ON lc.idProd = p.idProd
        GROUP BY p.idProd
        ORDER BY totalQty DESC
        LIMIT 5
    """)
    suspend fun getTopProducts(): List<TopProductStat>

    @Query("""
        SELECT c.nomCli || ' ' || c.prenomCli AS clientName, COUNT(cmd.idCmd) AS orderCount,
               COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS totalSpent
        FROM client c
        JOIN commande cmd ON c.idCli = cmd.idCli
        LEFT JOIN ligne_commande lc ON cmd.idCmd = lc.idCmd
        LEFT JOIN produit p ON lc.idProd = p.idProd
        GROUP BY c.idCli
        ORDER BY totalSpent DESC
    """)
    suspend fun getOrdersByClient(): List<ClientOrderStat>

    @Query("""
        SELECT r.nomRegion, COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS totalRevenue
        FROM region r
        JOIN client c ON r.idRegion = c.idRegion
        JOIN commande cmd ON c.idCli = cmd.idCli
        JOIN ligne_commande lc ON cmd.idCmd = lc.idCmd
        JOIN produit p ON lc.idProd = p.idProd
        GROUP BY r.idRegion
        ORDER BY totalRevenue DESC
    """)
    suspend fun getRevenueByRegion(): List<RegionRevenueStat>

    @Query("""
        SELECT strftime('%Y-%m', dateCmd / 1000, 'unixepoch') AS month,
               COUNT(*) AS orderCount,
               COALESCE(SUM(lc.qteCmd * p.prixUProd), 0) AS revenue
        FROM commande cmd
        LEFT JOIN ligne_commande lc ON cmd.idCmd = lc.idCmd
        LEFT JOIN produit p ON lc.idProd = p.idProd
        GROUP BY month
        ORDER BY month ASC
    """)
    suspend fun getMonthlyStats(): List<MonthlyStat>
}

data class TopProductStat(
    val designProd: String,
    val totalQty: Int,
    val totalRevenue: Double
)

data class ClientOrderStat(
    val clientName: String,
    val orderCount: Int,
    val totalSpent: Double
)

data class RegionRevenueStat(
    val nomRegion: String,
    val totalRevenue: Double
)

data class MonthlyStat(
    val month: String,
    val orderCount: Int,
    val revenue: Double
)
