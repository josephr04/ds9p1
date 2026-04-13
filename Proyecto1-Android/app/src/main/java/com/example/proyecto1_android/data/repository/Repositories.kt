// =====================================================================
// data/repository/ProductoRepository.kt
// Equivalente a tu clase Producto.php (model)
// Une la API con la UI — el ViewModel no habla directo con Retrofit
// =====================================================================
package com.example.proyecto1_android.data.repository


import com.example.proyecto1_android.data.api.RetrofitClient
import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.Producto

import com.example.proyecto1_android.data.db.CarritoDao
import com.example.proyecto1_android.model.CarritoItem

class ProductoRepository {

    private val api = RetrofitClient.instance

    // Todos los productos — GET /api/productos
    suspend fun getProductos(): Result<List<Producto>> {
        return try {
            val response = api.getProductos()
            if (response.isSuccessful) {
                Result.success(response.body() ?: emptyList())
            } else {
                Result.failure(Exception("Error ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // Producto individual — GET /api/productos/{id}
    suspend fun getProducto(id: Long): Result<Producto> {
        return try {
            val response = api.getProducto(id)
            if (response.isSuccessful && response.body() != null) {
                Result.success(response.body()!!)
            } else {
                Result.failure(Exception("Producto no encontrado"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // Categorías — GET /api/categorias
    suspend fun getCategorias(): Result<List<Categoria>> {
        return try {
            val response = api.getCategorias()
            if (response.isSuccessful) {
                Result.success(response.body() ?: emptyList())
            } else {
                Result.failure(Exception("Error al cargar categorías"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // Productos por categoría — GET /api/categorias/{id}/productos
    suspend fun getProductosPorCategoria(id: Int): Result<List<Producto>> {
        return try {
            val response = api.getProductosPorCategoria(id)
            if (response.isSuccessful) {
                Result.success(response.body() ?: emptyList())
            } else {
                Result.failure(Exception("Error ${response.code()}: ${response.message()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}


// =====================================================================
// data/repository/CarritoRepository.kt
// Equivalente a tu carritoController.php — todas las acciones del carrito
// =====================================================================

class CarritoRepository(private val dao: CarritoDao) {

    // LiveData reactivos — la UI se actualiza sola cuando cambian
    val items = dao.getAll()
    val cantidadTotal = dao.contarItems()
    val subtotal = dao.calcularSubtotal()

    // Agregar producto — equivalente a accion=agregar en carritoController.php
    // Si ya existe, suma la cantidad en vez de duplicar
    suspend fun agregar(producto: Producto, cantidad: Int = 1) {
        val existente = dao.getById(producto.id)
        if (existente != null) {
            // Ya está en carrito → sumar cantidad
            dao.update(existente.copy(cantidad = existente.cantidad + cantidad))
        } else {
            // Nuevo item
            dao.insert(
                CarritoItem(
                    idProducto = producto.id,
                    nombre = producto.nombre,
                    imagen = producto.imagen,
                    precioVenta = producto.precioVenta,
                    cantidad = cantidad
                )
            )
        }
    }

    // Sumar 1 — equivalente a accion=sumar
    suspend fun sumar(item: CarritoItem) {
        dao.update(item.copy(cantidad = item.cantidad + 1))
    }

    // Restar 1 — equivalente a accion=restar (elimina si llega a 0)
    suspend fun restar(item: CarritoItem) {
        if (item.cantidad > 1) {
            dao.update(item.copy(cantidad = item.cantidad - 1))
        } else {
            dao.delete(item)
        }
    }

    // Eliminar — equivalente a accion=eliminar
    suspend fun eliminar(item: CarritoItem) {
        dao.delete(item)
    }

    // Vaciar — equivalente a accion=vaciar
    suspend fun vaciar() {
        dao.vaciar()
    }
}