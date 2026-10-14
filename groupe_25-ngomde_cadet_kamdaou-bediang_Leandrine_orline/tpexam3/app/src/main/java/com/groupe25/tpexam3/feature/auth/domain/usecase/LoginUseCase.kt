package com.groupe25.tpexam3.feature.auth.domain.usecase

import com.groupe25.tpexam3.feature.auth.domain.model.User
import com.groupe25.tpexam3.feature.auth.domain.repository.AuthRepository

class LoginUseCase(private val repository: AuthRepository) {

    suspend operator fun invoke(login: String, password: String): Result<User> {
        if (login.isBlank() || password.isBlank()) {
            return Result.failure(IllegalArgumentException("empty_fields"))
        }
        val user = repository.login(login, password)
            ?: return Result.failure(IllegalArgumentException("invalid_credentials"))
        return Result.success(user)
    }
}
