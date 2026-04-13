// =====================================================================
// ui/detalle/DetalleViewModel.kt
// Maneja el estado de la pantalla de detalle
// Equivalente a la lógica PHP en detalle.php
// =====================================================================
package com.example.proyecto1_android.ui.detalle

import androidx.lifecycle.*
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.data.repository.ProductoRepository
import com.example.proyecto1_android.model.Producto
import kotlinx.coroutines.launch

class DetalleViewModel(
    private val productoRepo: ProductoRepository,
    private val carritoRepo: CarritoRepository
) : ViewModel() {

    private val _producto = MutableLiveData<Producto?>()
    val producto: LiveData<Producto?> = _producto

    private val _cargando = MutableLiveData(false)
    val cargando: LiveData<Boolean> = _cargando

    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    // Feedback al agregar al carrito
    private val _agregado = MutableLiveData(false)
    val agregado: LiveData<Boolean> = _agregado

    fun cargarProducto(id: Long) {
        viewModelScope.launch {
            _cargando.value = true
            productoRepo.getProducto(id).fold(
                onSuccess  = { _producto.value = it },
                onFailure  = { _error.value = it.message }
            )
            _cargando.value = false
        }
    }

    fun agregarAlCarrito(cantidad: Int = 1) {
        val p = _producto.value ?: return
        viewModelScope.launch {
            carritoRepo.agregar(p, cantidad)
            _agregado.value = true
        }
    }

    class Factory(
        private val productoRepo: ProductoRepository,
        private val carritoRepo: CarritoRepository
    ) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return DetalleViewModel(productoRepo, carritoRepo) as T
        }
    }
}