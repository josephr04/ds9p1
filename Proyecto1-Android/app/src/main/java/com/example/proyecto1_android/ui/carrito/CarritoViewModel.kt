// =====================================================================
// ui/carrito/CarritoViewModel.kt
// Maneja el estado del carrito
// Equivalente a la lógica PHP en carrito.php + carritoController.php
// =====================================================================
package com.example.proyecto1_android.ui.carrito

import androidx.lifecycle.*
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.model.CarritoItem
import kotlinx.coroutines.launch

class CarritoViewModel(private val repo: CarritoRepository) : ViewModel() {

    // Items reactivos — equivalente a foreach($_SESSION['carrito'])
    val items = repo.items
    val subtotal = repo.subtotal

    // ITBMS 7% — equivalente a $impuestos_tasa = 0.07 en carrito.php
    val impuestos: LiveData<Double> = subtotal.map { (it ?: 0.0) * 0.07 }
    val total: LiveData<Double> = subtotal.map { (it ?: 0.0) * 1.07 }

    fun sumar(item: CarritoItem) = viewModelScope.launch { repo.sumar(item) }
    fun restar(item: CarritoItem) = viewModelScope.launch { repo.restar(item) }
    fun eliminar(item: CarritoItem) = viewModelScope.launch { repo.eliminar(item) }
    fun vaciar() = viewModelScope.launch { repo.vaciar() }

    class Factory(private val repo: CarritoRepository) : ViewModelProvider.Factory {
        override fun <T : ViewModel> create(modelClass: Class<T>): T {
            @Suppress("UNCHECKED_CAST")
            return CarritoViewModel(repo) as T
        }
    }
}