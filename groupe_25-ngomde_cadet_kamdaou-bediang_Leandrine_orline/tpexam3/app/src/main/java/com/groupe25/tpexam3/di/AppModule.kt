package com.groupe25.tpexam3.di

import com.groupe25.tpexam3.core.data.local.AppDatabase
import com.groupe25.tpexam3.feature.auth.data.repository.AuthRepositoryImpl
import com.groupe25.tpexam3.feature.auth.domain.repository.AuthRepository
import com.groupe25.tpexam3.feature.auth.domain.usecase.LoginUseCase
import com.groupe25.tpexam3.feature.auth.presentation.LoginViewModel
import com.groupe25.tpexam3.feature.client.presentation.ClientListViewModel
import com.groupe25.tpexam3.feature.dashboard.presentation.DashboardViewModel
import com.groupe25.tpexam3.feature.order.data.repository.OrderRepositoryImpl
import com.groupe25.tpexam3.feature.order.domain.repository.OrderRepository
import com.groupe25.tpexam3.feature.order.presentation.create.CreateOrderViewModel
import com.groupe25.tpexam3.feature.order.presentation.detail.OrderDetailViewModel
import com.groupe25.tpexam3.feature.order.presentation.list.OrderListViewModel
import com.groupe25.tpexam3.feature.product.data.repository.ProductRepositoryImpl
import com.groupe25.tpexam3.feature.product.domain.repository.ProductRepository
import com.groupe25.tpexam3.feature.product.presentation.addedit.AddEditProductViewModel
import com.groupe25.tpexam3.feature.product.presentation.list.ProductListViewModel
import org.koin.android.ext.koin.androidContext
import org.koin.androidx.viewmodel.dsl.viewModel
import org.koin.dsl.module

val appModule = module {
    // Database
    single { AppDatabase.create(androidContext()) }
    single { get<AppDatabase>().userDao() }
    single { get<AppDatabase>().productDao() }
    single { get<AppDatabase>().clientDao() }
    single { get<AppDatabase>().commandeDao() }
    single { get<AppDatabase>().ligneCommandeDao() }
    single { get<AppDatabase>().dashboardDao() }

    // Auth
    single<AuthRepository> { AuthRepositoryImpl(get()) }
    factory { LoginUseCase(get()) }
    viewModel { LoginViewModel(get()) }

    // Product
    single<ProductRepository> { ProductRepositoryImpl(get()) }
    viewModel { ProductListViewModel(get()) }
    viewModel { AddEditProductViewModel(get(), get()) }

    // Order
    single<OrderRepository> { OrderRepositoryImpl(get(), get()) }
    viewModel { OrderListViewModel(get()) }
    viewModel { OrderDetailViewModel(get(), get(), get()) }
    viewModel { CreateOrderViewModel(get(), get()) }

    // Client
    viewModel { ClientListViewModel(get()) }

    // Dashboard
    viewModel { DashboardViewModel(get()) }
}
