// =====================================================================
// ui/admin/AdminMarcasViewModel.kt
// =====================================================================
package com.example.proyecto1_android.ui.admin

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.model.Marca
import com.example.proyecto1_android.model.MarcaRequest
import kotlinx.coroutines.launch

class AdminMarcasViewModel : ViewModel() {

    private val api = RetrofitClient.instance

    private val _marcas = MutableLiveData<List<Marca>>()
    val marcas: LiveData<List<Marca>> = _marcas

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
                val r = api.getMarcas()
                if (r.isSuccessful) _marcas.value = r.body() ?: emptyList()
                else _error.value = "Error al cargar (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun crear(nombre: String) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.crearMarca(MarcaRequest(nombre))
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Marca creada"
                    cargar()
                } else _error.value = "Error al crear (${r.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun editar(id: Int, nombre: String) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val r = api.actualizarMarca(id, MarcaRequest(nombre))
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Marca actualizada"
                    cargar()
                } else _error.value = "Error al editar (${r.code()})"
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
                val r = api.eliminarMarca(id)
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Marca eliminada"
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