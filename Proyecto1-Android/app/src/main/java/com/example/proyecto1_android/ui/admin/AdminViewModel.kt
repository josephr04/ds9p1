// =====================================================================
// ui/admin/AdminViewModel.kt — con búsqueda y filtro por categoría
// =====================================================================
package com.example.proyecto1_android.ui.admin

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.Producto
import com.example.proyecto1_android.model.ProductoRequest
import kotlinx.coroutines.launch

class AdminViewModel : ViewModel() {

    private val api = RetrofitClient.instance

    // Lista completa sin filtrar
    private val todosLosProductos = mutableListOf<Producto>()

    private val _productos = MutableLiveData<List<Producto>>()
    val productos: LiveData<List<Producto>> = _productos

    private val _categorias = MutableLiveData<List<Categoria>>()
    val categorias: LiveData<List<Categoria>> = _categorias

    private val _isLoading = MutableLiveData<Boolean>()
    val isLoading: LiveData<Boolean> = _isLoading

    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    private val _operacionExitosa = MutableLiveData<Boolean?>()
    val operacionExitosa: LiveData<Boolean?> = _operacionExitosa

    // Estado actual de filtros
    private var categoriaSeleccionada: Int? = null  // null = todas
    private var textoBusqueda: String = ""

    // ── Cargar productos ──────────────────────────────────────────
    fun cargarProductos() {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val response = api.getProductos()
                if (response.isSuccessful) {
                    val lista = response.body() ?: emptyList()
                    todosLosProductos.clear()
                    todosLosProductos.addAll(lista)
                    aplicarFiltros()
                } else {
                    _error.value = "Error al cargar productos (${response.code()})"
                }
            } catch (e: Exception) {
                _error.value = "Error de conexión: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    // ── Cargar categorías para los chips ──────────────────────────
    fun cargarCategorias() {
        viewModelScope.launch {
            try {
                val response = api.getCategorias()
                if (response.isSuccessful) {
                    _categorias.value = response.body() ?: emptyList()
                }
            } catch (e: Exception) {
                // No crítico
            }
        }
    }

    // ── Filtrar por categoría ─────────────────────────────────────
    fun filtrarPorCategoria(idCategoria: Int?) {
        categoriaSeleccionada = idCategoria
        aplicarFiltros()
    }

    // ── Buscar por nombre ─────────────────────────────────────────
    fun buscar(texto: String) {
        textoBusqueda = texto.trim().lowercase()
        aplicarFiltros()
    }

    // ── Aplica ambos filtros sobre la lista completa ──────────────
    private fun aplicarFiltros() {
        var resultado = todosLosProductos.toList()

        // Filtro por categoría
        if (categoriaSeleccionada != null) {
            resultado = resultado.filter { it.idCategoria == categoriaSeleccionada }
        }

        // Filtro por nombre
        if (textoBusqueda.isNotEmpty()) {
            resultado = resultado.filter {
                it.nombre.lowercase().contains(textoBusqueda)
            }
        }

        _productos.value = resultado
    }

    // ── CRUD ──────────────────────────────────────────────────────
    fun crearProducto(request: ProductoRequest) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val response = api.crearProducto(request)
                if (response.isSuccessful) _operacionExitosa.value = true
                else _error.value = "Error al crear (${response.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun actualizarProducto(id: Long, request: ProductoRequest) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val response = api.actualizarProducto(id, request)
                if (response.isSuccessful) _operacionExitosa.value = true
                else _error.value = "Error al actualizar (${response.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun eliminarProducto(id: Long) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val response = api.eliminarProducto(id)
                if (response.isSuccessful) _operacionExitosa.value = true
                else _error.value = "Error al eliminar (${response.code()})"
            } catch (e: Exception) {
                _error.value = "Error: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun clearError() { _error.value = null }
    fun resetOperacion() { _operacionExitosa.value = null }

    // ── Producto individual para el formulario de edición ────────────
    private val _productoSeleccionado = MutableLiveData<Producto?>()
    val productoSeleccionado: LiveData<Producto?> = _productoSeleccionado

    fun cargarProducto(id: Long) {
        _isLoading.value = true
        viewModelScope.launch {
            try {
                val response = api.getProducto(id)
                if (response.isSuccessful && response.body() != null) {
                    _productoSeleccionado.value = response.body()
                } else {
                    _error.value = "Producto no encontrado (${response.code()})"
                }
            } catch (e: Exception) {
                _error.value = "Error de conexión: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }
}