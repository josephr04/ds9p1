// =====================================================================
// data/api/ApiService.kt
// Equivalente a tus controllers PHP — define qué endpoints consume la app
// Cada función = un endpoint de tu Laravel
// =====================================================================
package com.example.proyecto1_android.data.api

import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.Producto
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Path

import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.Query

interface ApiService {

    // ── GET /api/productos ────────────────────────────────────────
    // Equivalente a: SELECT * FROM productos en tu tienda.php
    @GET("api/productos")
    suspend fun getProductos(): Response<List<Producto>>

    // ── GET /api/productos/{id} ───────────────────────────────────
    // Equivalente a: SELECT * FROM productos WHERE idProducto = $id
    @GET("api/productos/{id}")
    suspend fun getProducto(@Path("id") id: Long): Response<Producto>

    // ── GET /api/categorias ───────────────────────────────────────
    // Para el sidebar de filtros
    @GET("api/categorias")
    suspend fun getCategorias(): Response<List<Categoria>>

    // ── GET /api/categorias/{id}/productos ────────────────────────
    // Equivalente a: WHERE p.idCategoria = $idCatSeleccionada
    @GET("api/productos")
    suspend fun getProductosPorCategoria(@Query("categoria_id") categoriaId: Int): Response<List<Producto>>
}


// =====================================================================
// data/api/RetrofitClient.kt
// Configuración del cliente HTTP — equivalente a tu config/conexion.php
// =====================================================================

object RetrofitClient {

    // ⚠️ IMPORTANTE:
    // - Emulador Android  → usar 10.0.2.2 (apunta a localhost de tu PC)
    // - Dispositivo físico → usar la IP de tu PC en la red (ej: 192.168.1.5)
    // - Puerto 8000 = Laravel artisan serve (ajústalo si usas otro puerto)
    private const val BASE_URL = "http://10.0.2.2:8000/"

    // Logger para ver las peticiones en Logcat (útil para depurar)
    private val loggingInterceptor = HttpLoggingInterceptor().apply {
        level = HttpLoggingInterceptor.Level.BODY
    }

    private val httpClient = OkHttpClient.Builder()
        .addInterceptor(loggingInterceptor)
        .build()

    val instance: ApiService by lazy {
        Retrofit.Builder()
            .baseUrl(BASE_URL)
            .client(httpClient)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }
}