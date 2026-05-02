package com.example.proyecto1_android.ui.checkout

import androidx.lifecycle.LiveData
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import androidx.lifecycle.map
import androidx.lifecycle.viewModelScope
import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.model.CarritoItem
import com.example.proyecto1_android.model.DetalleRequest
import com.example.proyecto1_android.model.FacturaRequest
import com.example.proyecto1_android.model.FacturaResponse
import com.example.proyecto1_android.model.Tarjeta
import kotlinx.coroutines.launch

class CheckoutViewModel(
    private val carritoRepo: CarritoRepository
) : ViewModel() {

    private val api = RetrofitClient.instance

    val items: LiveData<List<CarritoItem>> = carritoRepo.items

    val subtotal: LiveData<Double> = carritoRepo.subtotal.map { it ?: 0.0 }
    val itbms:    LiveData<Double> = subtotal.map { it * 0.07 }
    val total:    LiveData<Double> = subtotal.map { it * 1.07 }

    private val _tarjetas = MutableLiveData<List<Tarjeta>>()
    val tarjetas: LiveData<List<Tarjeta>> = _tarjetas

    private val _isLoading = MutableLiveData(false)
    val isLoading: LiveData<Boolean> = _isLoading

    private val _error = MutableLiveData<String?>()
    val error: LiveData<String?> = _error

    // ✅ Reemplaza _exitoso por _facturaCreada
    private val _facturaCreada = MutableLiveData<FacturaResponse?>()
    val facturaCreada: LiveData<FacturaResponse?> = _facturaCreada

    var metodoPago: String = "tarjeta"

    init { cargarTarjetas() }

    private fun cargarTarjetas() {
        viewModelScope.launch {
            try {
                val r = api.getTarjetas()
                if (r.isSuccessful) _tarjetas.value = r.body() ?: emptyList()
                else _error.value = "Error al cargar tarjetas (${r.code()})"
            } catch (e: Exception) {
                _error.value = "No se pudieron cargar las tarjetas"
            }
        }
    }

    fun pagar(idTarjeta: Long) {
        val itemsActuales = items.value ?: return
        if (itemsActuales.isEmpty()) { _error.value = "El carrito está vacío"; return }

        _isLoading.value = true
        viewModelScope.launch {
            try {
                val request = FacturaRequest(
                    idTarjeta = idTarjeta,
                    detalles  = itemsActuales.map {
                        DetalleRequest(it.idProducto, it.cantidad, it.precioVenta)
                    }
                )
                val r = api.crearFactura(request)
                if (r.isSuccessful) {
                    carritoRepo.vaciar()
                    // ✅ Guarda la respuesta de la factura en lugar de solo true
                    _facturaCreada.value = r.body()
                } else {
                    val msg = r.errorBody()?.string()
                        ?.let { org.json.JSONObject(it).optString("mensaje") }
                        ?: "Error al procesar el pago (${r.code()})"
                    _error.value = msg
                }
            } catch (e: Exception) {
                _error.value = "Error de conexión: ${e.message}"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun clearError() { _error.value = null }

    class Factory(private val repo: CarritoRepository) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return CheckoutViewModel(repo) as T
        }
    }
}