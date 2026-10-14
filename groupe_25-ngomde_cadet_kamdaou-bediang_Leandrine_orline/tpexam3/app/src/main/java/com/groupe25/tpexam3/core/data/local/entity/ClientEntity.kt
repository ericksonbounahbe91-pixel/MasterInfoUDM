package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "client",
    foreignKeys = [
        ForeignKey(
            entity = RegionEntity::class,
            parentColumns = ["idRegion"],
            childColumns = ["idRegion"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index("idRegion")]
)
data class ClientEntity(
    @PrimaryKey(autoGenerate = true) val idCli: Int = 0,
    val nomCli: String,
    val prenomCli: String,
    val email: String,
    val photo: String? = null,
    val idRegion: Int
)
