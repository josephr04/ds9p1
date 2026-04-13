// =====================================================================
// model/CarritoItem.kt
// Entidad Room — equivalente a $_SESSION['carrito'] en tu PHP
// El carrito vive LOCAL en el celular (no necesita endpoint en Laravel)
// =====================================================================
package com.example.proyecto1_android.model

import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "carrito")
data class CarritoItem(
    @PrimaryKey
    val idProducto: Long,
    val nombre: String,
    val imagen: String,
    val precioVenta: Double,
    var cantidad: Int = 1
) {
    // Subtotal de esta fila — equivalente a $item['precio'] * $item['cantidad']
    fun subtotal(): Double = precioVenta * cantidad

    // URL de imagen
    fun imageUrl(): String = "file:///android_asset/$imagen"
}