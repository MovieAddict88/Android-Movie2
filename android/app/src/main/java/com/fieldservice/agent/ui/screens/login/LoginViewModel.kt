package com.fieldservice.agent.ui.screens.login

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.fieldservice.agent.data.remote.api.ApiService
import com.fieldservice.agent.data.remote.api.LoginRequest
import com.fieldservice.agent.data.repository.AuthRepository
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val apiService: ApiService,
    private val authRepository: AuthRepository
) : ViewModel() {

    private val _loginState = MutableStateFlow<LoginState>(LoginState.Idle)
    val loginState: StateFlow<LoginState> = _loginState.asStateFlow()

    private val _formState = MutableStateFlow(LoginFormState())
    val formState: StateFlow<LoginFormState> = _formState.asStateFlow()

    val isLoggedIn: StateFlow<Boolean> = authRepository.isLoggedIn
        .stateIn(viewModelScope, SharingStarted.Eagerly, false)

    fun onEmailChange(email: String) {
        _formState.value = _formState.value.copy(email = email, emailError = null)
    }

    fun onPasswordChange(password: String) {
        _formState.value = _formState.value.copy(password = password, passwordError = null)
    }

    fun login() {
        val form = _formState.value
        
        // Validate
        val emailError = validateEmail(form.email)
        val passwordError = validatePassword(form.password)
        
        if (emailError != null || passwordError != null) {
            _formState.value = form.copy(emailError = emailError, passwordError = passwordError)
            return
        }

        viewModelScope.launch {
            _loginState.value = LoginState.Loading
            _formState.value = _formState.value.copy(isLoading = true)

            try {
                val response = apiService.login(
                    LoginRequest(
                        email = form.email.trim(),
                        password = form.password,
                        deviceToken = authRepository.getSavedDeviceToken(),
                        deviceName = android.os.Build.MODEL,
                        deviceType = "android"
                    )
                )

                if (response.isSuccessful) {
                    val body = response.body()
                    if (body?.success == true && body.token != null && body.user != null) {
                        authRepository.saveAuthToken(body.token)
                        authRepository.saveUser(body.user)
                        _loginState.value = LoginState.Success
                    } else {
                        _loginState.value = LoginState.Error(
                            body?.message ?: "Login failed. Please check your credentials."
                        )
                    }
                } else {
                    val errorMessage = when (response.code()) {
                        401 -> "Invalid email or password"
                        429 -> "Too many login attempts. Please wait a moment."
                        500 -> "Server error. Please try again later."
                        else -> "Login failed. Please try again."
                    }
                    _loginState.value = LoginState.Error(errorMessage)
                }
            } catch (e: Exception) {
                val errorMessage = when {
                    e.message?.contains("Unable to resolve host") == true -> 
                        "No internet connection. Please check your network."
                    e.message?.contains("timeout") == true -> 
                        "Request timed out. Please try again."
                    else -> e.message ?: "Network error occurred"
                }
                _loginState.value = LoginState.Error(errorMessage)
            } finally {
                _formState.value = _formState.value.copy(isLoading = false)
            }
        }
    }

    fun logout() {
        viewModelScope.launch {
            _formState.value = _formState.value.copy(isLoading = true)
            try {
                authRepository.logout()
            } catch (e: Exception) {
                // Continue even if API fails
            } finally {
                _formState.value = LoginFormState()
                _loginState.value = LoginState.Idle
            }
        }
    }

    fun resetState() {
        _loginState.value = LoginState.Idle
    }

    fun clearError() {
        _loginState.value = LoginState.Idle
        if (_loginState.value is LoginState.Error) {
            _loginState.value = LoginState.Idle
        }
    }

    private fun validateEmail(email: String): String? {
        return when {
            email.isBlank() -> "Email is required"
            !android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches() -> 
                "Please enter a valid email address"
            else -> null
        }
    }

    private fun validatePassword(password: String): String? {
        return when {
            password.isBlank() -> "Password is required"
            password.length < 6 -> "Password must be at least 6 characters"
            else -> null
        }
    }
}

sealed class LoginState {
    data object Idle : LoginState()
    data object Loading : LoginState()
    data object Success : LoginState()
    data class Error(val message: String) : LoginState()
}

data class LoginFormState(
    val email: String = "",
    val password: String = "",
    val emailError: String? = null,
    val passwordError: String? = null,
    val isLoading: Boolean = false
)