package com.groupe25.tpexam3.di

import com.groupe25.tpexam3.core.data.local.AppDatabase
import com.groupe25.tpexam3.feature.auth.data.repository.AuthRepositoryImpl
import com.groupe25.tpexam3.feature.auth.domain.repository.AuthRepository
import com.groupe25.tpexam3.feature.auth.domain.usecase.LoginUseCase
import com.groupe25.tpexam3.feature.auth.presentation.LoginViewModel
import org.koin.android.ext.koin.androidContext
import org.koin.androidx.viewmodel.dsl.viewModel
import org.koin.dsl.module

val appModule = module {
    // Database
    single { AppDatabase.create(androidContext()) }
    single { get<AppDatabase>().userDao() }
    single { get<AppDatabase>().productDao() }

    // Auth
    single<AuthRepository> { AuthRepositoryImpl(get()) }
    factory { LoginUseCase(get()) }
    viewModel { LoginViewModel(get()) }
}
