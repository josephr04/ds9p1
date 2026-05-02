// =====================================================================
// ui/admin/AdminEmpleadosViewModel.kt
// =====================================================================
package com.example.proyecto1_android.ui.admin

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.model.Empleado
import com.example.proyecto1_android.model.EmpleadoRequest
import kotlinx.coroutines.launch

class AdminEmpleadosViewModel : ViewModel() {

    private val api = RetrofitClient.instance

    private val _empleados = MutableLiveData<List<Empleado>>()
    val empleados: LiveData<List<Empleado>> = _empleados

    private val _isLoading = MutableLiveData<Boolean>()
    val isLoading: LiveData<Boolean> = _isLoading

    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    private val _operacionExitosa = MutableLiveData<String?>()
    val operacionExitosa: LiveData<String?> = _operacionExitosa

    fun cargar() {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.getEmpleados()
                if (r.isSuccessful) _empleados.value = r.body() ?: emptyList()
                else _error.value = "Error al cargar empleados (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error de conexión: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun crear(request: EmpleadoRequest) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.crearEmpleado(request)
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Empleado creado"
                    cargar()
                } else _error.value = "Error al crear (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun editar(id: Int, request: EmpleadoRequest) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.actualizarEmpleado(id, request)
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Empleado actualizado"
                    cargar()
                } else _error.value = "Error al actualizar (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun eliminar(id: Int) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.eliminarEmpleado(id)
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Empleado eliminado"
                    cargar()
                } else _error.value = "Error al eliminar (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun clearMensaje() { _operacionExitosa.value = null }
    fun clearError() { _error.value = null }
}