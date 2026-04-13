// =====================================================================
// data/db/CarritoDao.kt
// DAO = Data Access Object — las "queries" del carrito local
// Equivalente a tu carritoController.php
// =====================================================================
package com.example.proyecto1_android.data.db

import androidx.lifecycle.LiveData
import androidx.room.*
import com.example.proyecto1_android.model.CarritoItem

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase

@Dao
interface CarritoDao {

    // Obtener todos los items — equivalente a foreach($_SESSION['carrito'])
    @Query("SELECT * FROM carrito")
    fun getAll(): LiveData<List<CarritoItem>>

    // Obtener un item por ID — para saber si ya existe en el carrito
    @Query("SELECT * FROM carrito WHERE idProducto = :id LIMIT 1")
    suspend fun getById(id: Long): CarritoItem?

    // Insertar o reemplazar — equivalente a accion=agregar
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insert(item: CarritoItem)

    // Actualizar cantidad — equivalente a accion=sumar/restar
    @Update
    suspend fun update(item: CarritoItem)

    // Eliminar un item — equivalente a accion=eliminar
    @Delete
    suspend fun delete(item: CarritoItem)

    // Vaciar carrito — equivalente a accion=vaciar
    @Query("DELETE FROM carrito")
    suspend fun vaciar()

    // Contar items totales (para el badge del toolbar)
    // Equivalente a $cartCount en tu head.php
    @Query("SELECT SUM(cantidad) FROM carrito")
    fun contarItems(): LiveData<Int?>

    // Calcular subtotal
    @Query("SELECT SUM(precioVenta * cantidad) FROM carrito")
    fun calcularSubtotal(): LiveData<Double?>
}


// =====================================================================
// data/db/AppDatabase.kt
// Base de datos Room — equivalente a tu config/conexion.php pero local
// =====================================================================

@Database(entities = [CarritoItem::class], version = 1, exportSchema = false)
abstract class AppDatabase : RoomDatabase() {

    abstract fun carritoDao(): CarritoDao

    companion object {
        @Volatile
        private var INSTANCE: AppDatabase? = null

        fun getInstance(context: Context): AppDatabase {
            return INSTANCE ?: synchronized(this) {
                Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "ecommerce_db"
                ).build().also { INSTANCE = it }
            }
        }
    }
}