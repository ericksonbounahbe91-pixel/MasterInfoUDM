package com.groupe25.tpexam3.feature.auth.domain.repository

import com.groupe25.tpexam3.feature.auth.domain.model.User

interface AuthRepository {
    suspend fun login(login: String, password: String): User?
}
