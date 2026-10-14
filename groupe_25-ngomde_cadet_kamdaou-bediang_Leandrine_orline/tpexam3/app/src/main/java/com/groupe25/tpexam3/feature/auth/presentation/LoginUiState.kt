package com.groupe25.tpexam3.feature.auth.presentation

import com.groupe25.tpexam3.core.presentation.UiText

data class LoginUiState(
    val login: String = "",
    val password: String = "",
    val isLoading: Boolean = false,
    val error: UiText? = null,
    val isLoggedIn: Boolean = false
)
