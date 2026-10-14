package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.ForeignKey
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "region",
    foreignKeys = [
        ForeignKey(
            entity = DepartmentEntity::class,
            parentColumns = ["idDep"],
            childColumns = ["idDep"],
            onDelete = ForeignKey.CASCADE
        )
    ],
    indices = [Index("idDep")]
)
data class RegionEntity(
    @PrimaryKey(autoGenerate = true) val idRegion: Int = 0,
    val nomRegion: String,
    val idDep: Int
)
