package com.groupe25.tpexam3.core.data.local.dao

import androidx.room.Dao
import androidx.room.Query
import com.groupe25.tpexam3.core.data.local.entity.ClientEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface ClientDao {
    @Query("""
        SELECT c.*, r.nomRegion
        FROM client c
        LEFT JOIN region r ON c.idRegion = r.idRegion
        ORDER BY c.nomCli ASC
    """)
    fun getAllClientsWithRegion(): Flow<List<ClientWithRegion>>

    @Query("SELECT * FROM client ORDER BY nomCli ASC")
    fun getAllClients(): Flow<List<ClientEntity>>

    @Query("SELECT * FROM client WHERE idCli = :id")
    suspend fun getClientById(id: Int): ClientEntity?
}

data class ClientWithRegion(
    val idCli: Int,
    val nomCli: String,
    val prenomCli: String,
    val email: String,
    val photo: String?,
    val idRegion: Int,
    val nomRegion: String?
)
