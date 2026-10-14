package com.groupe25.tpexam3.core.data.local.entity

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "departement")
data class DepartmentEntity(
    @PrimaryKey(autoGenerate = true) val idDep: Int = 0,
    val nomDep: String
)
