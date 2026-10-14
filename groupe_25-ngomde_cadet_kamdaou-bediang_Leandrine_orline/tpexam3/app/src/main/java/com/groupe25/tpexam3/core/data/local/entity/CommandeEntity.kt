package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "commande",
    foreignKeys = [
        ForeignKey(
            entity = ClientEntity::class,
            parentColumns = ["idCli"],
            childColumns = ["idCli"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index("idCli")]
)
data class CommandeEntity(
    @PrimaryKey(autoGenerate = true) val idCmd: Int = 0,
    val dateCmd: Long,
    val idCli: Int
)
