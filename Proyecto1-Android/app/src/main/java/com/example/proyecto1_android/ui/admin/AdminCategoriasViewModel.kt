// =====================================================================
// ui/admin/AdminCategoriasViewModel.kt
// =====================================================================
package com.example.proyecto1_android.ui.admin

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.CategoriaRequest
import kotlinx.coroutines.launch

class AdminCategoriasViewModel : ViewModel() {

    private val api = RetrofitClient.instance

    private val _categorias = MutableLiveData<List<Categoria>>()
    val categorias: LiveData<List<Categoria>> = _categorias

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
                val r = api.getCategorias()
                if (r.isSuccessful) _categorias.value = r.body() ?: emptyList()
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
                val r = api.crearCategoria(CategoriaRequest(nombre))
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Categoría creada"
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
                val r = api.actualizarCategoria(id, CategoriaRequest(nombre))
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Categoría actualizada"
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
                val r = api.eliminarCategoria(id)
                if (r.isSuccessful) {
                    _operacionExitosa.value = "Categoría eliminada"
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