package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index

@Entity(
    tableName = "ligne_commande",
    primaryKeys = ["idCmd", "idProd"],
    foreignKeys = [
        ForeignKey(
            entity = CommandeEntity::class,
            parentColumns = ["idCmd"],
            childColumns = ["idCmd"],
            onDelete = ForeignKey.CASCADE
        ),
        ForeignKey(
            entity = ProductEntity::class,
            parentColumns = ["idProd"],
            childColumns = ["idProd"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index("idCmd"), Index("idProd")]
)
data class LigneCommandeEntity(
    val idCmd: Int,
    val idProd: Int,
    val qteCmd: Int
)
