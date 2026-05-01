// =====================================================================
// ui/tienda/TiendaViewModel.kt — con búsqueda local por nombre
// Solo se agrega búsqueda, el resto queda igual que el original
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

    private val _cargando = MutableLiveData(false)
    val cargando: LiveData<Boolean> = _cargando

    // Lista completa sin filtrar por texto (viene de la API)
    private val productosCargados = mutableListOf<Producto>()

    private val _productos = MutableLiveData<List<Producto>>()
    val productos: LiveData<List<Producto>> = _productos

    private val _categorias = MutableLiveData<List<Categoria>>()
    val categorias: LiveData<List<Categoria>> = _categorias

    private val _categoriaActual = MutableLiveData<Int?>(null)
    val categoriaActual: LiveData<Int?> = _categoriaActual

    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    val cantidadCarrito = carritoRepo.cantidadTotal

    // Texto de búsqueda actual
    private var textoBusqueda: String = ""

    init {
        cargarCategorias()
        cargarProductos()
    }

    // ── Carga desde la API y guarda la lista completa ─────────────
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
                onSuccess = { lista ->
                    productosCargados.clear()
                    productosCargados.addAll(lista)
                    aplicarBusqueda() // aplica el texto si hay uno activo
                },
                onFailure = { _error.value = it.message }
            )
            _cargando.value = false
        }
    }

    // ── Filtra localmente por nombre (instantáneo, sin API) ───────
    fun buscar(texto: String) {
        textoBusqueda = texto.trim().lowercase()
        aplicarBusqueda()
    }

    private fun aplicarBusqueda() {
        _productos.value = if (textoBusqueda.isEmpty()) {
            productosCargados.toList()
        } else {
            productosCargados.filter {
                it.nombre.lowercase().contains(textoBusqueda)
            }
        }
    }

    private fun cargarCategorias() {
        viewModelScope.launch {
            productoRepo.getCategorias().fold(
                onSuccess = { _categorias.value = it },
                onFailure = { }
            )
        }
    }

    fun agregarAlCarrito(producto: Producto) {
        viewModelScope.launch {
            carritoRepo.agregar(producto)
        }
    }

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