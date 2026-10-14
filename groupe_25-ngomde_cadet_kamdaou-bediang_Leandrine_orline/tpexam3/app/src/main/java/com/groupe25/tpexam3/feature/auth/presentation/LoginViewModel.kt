package com.groupe25.tpexam3.feature.auth.presentation

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.groupe25.tpexam3.R
import com.groupe25.tpexam3.core.presentation.UiText
import com.groupe25.tpexam3.feature.auth.domain.usecase.LoginUseCase
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

class LoginViewModel(private val loginUseCase: LoginUseCase) : ViewModel() {

    private val _state = MutableStateFlow(LoginUiState())
    val state: StateFlow<LoginUiState> = _state.asStateFlow()

    fun onLoginChange(value: String) {
        _state.update { it.copy(login = value, error = null) }
    }

    fun onPasswordChange(value: String) {
        _state.update { it.copy(password = value, error = null) }
    }

    fun onLoginClick() {
        viewModelScope.launch {
            _state.update { it.copy(isLoading = true, error = null) }
            val result = loginUseCase(_state.value.login, _state.value.password)
            result.fold(
                onSuccess = {
                    _state.update { it.copy(isLoading = false, isLoggedIn = true) }
                },
                onFailure = { e ->
                    val error = when (e.message) {
                        "empty_fields" -> UiText.StringResource(R.string.login_error_empty)
                        else -> UiText.StringResource(R.string.login_error_invalid)
                    }
                    _state.update { it.copy(isLoading = false, error = error) }
                }
            )
        }
    }
}
