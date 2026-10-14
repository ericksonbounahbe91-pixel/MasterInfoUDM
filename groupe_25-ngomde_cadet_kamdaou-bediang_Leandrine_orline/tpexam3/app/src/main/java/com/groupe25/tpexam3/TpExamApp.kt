package com.groupe25.tpexam3

import android.app.Application
import com.groupe25.tpexam3.di.appModule
import org.koin.android.ext.koin.androidContext
import org.koin.core.context.startKoin

class TpExamApp : Application() {
    override fun onCreate() {
        super.onCreate()
        startKoin {
            androidContext(this@TpExamApp)
            modules(appModule)
        }
    }
}
