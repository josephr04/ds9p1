// =====================================================================
// ui/tienda/TiendaViewModel.kt
// Maneja el estado de la pantalla de catálogo
// Equivalente a la lógica PHP en la parte superior de tienda.php
// =====================================================================
package com.example.proyecto1_android.ui.tienda

import androidx.lifecycle.*
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.data.repository.ProductoRepository
import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.Producto
import kotlinx.coroutines.launch

class TiendaViewModel(
    private val productoRepo: ProductoRepository,
    private val carritoRepo: CarritoRepository
) : ViewModel() {

    // Estado de carga
    private val _cargando = MutableLiveData(false)
    val cargando: LiveData<Boolean> = _cargando

    // Lista de productos
    private val _productos = MutableLiveData<List<Producto>>()
    val productos: LiveData<List<Producto>> = _productos

    // Categorías para el chip/filter bar
    private val _categorias = MutableLiveData<List<Categoria>>()
    val categorias: LiveData<List<Categoria>> = _categorias

    // Categoría seleccionada (null = todas)
    private val _categoriaActual = MutableLiveData<Int?>(null)
    val categoriaActual: LiveData<Int?> = _categoriaActual

    // Error para mostrar Snackbar
    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    // Badge del carrito
    val cantidadCarrito = carritoRepo.cantidadTotal

    init {
        cargarCategorias()
        cargarProductos()
    }

    fun cargarProductos(idCategoria: Int? = null) {
        viewModelScope.launch {
            _cargando.value = true
            _categoriaActual.value = idCategoria

            val result = if (idCategoria == null) {
                productoRepo.getProductos()
            } else {
                productoRepo.getProductosPorCategoria(idCategoria)
            }

            result.fold(
                onSuccess = { _productos.value = it },
                onFailure = { _error.value = it.message }
            )
            _cargando.value = false
        }
    }

    private fun cargarCategorias() {
        viewModelScope.launch {
            productoRepo.getCategorias().fold(
                onSuccess = { _categorias.value = it },
                onFailure = { /* silencioso, no crítico */ }
            )
        }
    }

    fun agregarAlCarrito(producto: Producto) {
        viewModelScope.launch {
            carritoRepo.agregar(producto)
        }
    }

    // Factory para instanciar el ViewModel con parámetros
    class Factory(
        private val productoRepo: ProductoRepository,
        private val carritoRepo: CarritoRepository
    ) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return TiendaViewModel(productoRepo, carritoRepo) as T
        }
    }
}