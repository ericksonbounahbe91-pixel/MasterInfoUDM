package com.groupe25.tpexam3.feature.auth.data.repository

import com.groupe25.tpexam3.core.data.local.dao.UserDao
import com.groupe25.tpexam3.feature.auth.domain.model.User
import com.groupe25.tpexam3.feature.auth.domain.repository.AuthRepository

class AuthRepositoryImpl(private val userDao: UserDao) : AuthRepository {

    override suspend fun login(login: String, password: String): User? {
        val entity = userDao.authenticate(login, password) ?: return null
        return User(id = entity.id, login = entity.login)
    }
}
