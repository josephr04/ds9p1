// =====================================================================
// data/api/ApiService.kt — ACTUALIZADO con CRUD de productos
// =====================================================================
package com.example.proyecto1_android.data.api

import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.LoginResponse
import com.example.proyecto1_android.model.Producto
import com.example.proyecto1_android.model.ProductoRequest
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ── Tienda (ya existentes) ────────────────────────────────────
    @GET("api/productos")
    suspend fun getProductos(): Response<List<Producto>>

    @GET("api/productos/{id}")
    suspend fun getProducto(@Path("id") id: Long): Response<Producto>

    @GET("api/categorias")
    suspend fun getCategorias(): Response<List<Categoria>>

    @GET("api/productos")
    suspend fun getProductosPorCategoria(
        @Query("categoria_id") categoriaId: Int
    ): Response<List<Producto>>

    @FormUrlEncoded
    @POST("api/login")
    suspend fun login(
        @Field("usuario") usuario: String,
        @Field("contrasena") contrasena: String
    ): Response<LoginResponse>

    // ── CRUD Admin ────────────────────────────────────────────────

    // POST /api/productos — crear producto
    @POST("api/productos")
    suspend fun crearProducto(@Body producto: ProductoRequest): Response<Producto>

    // PUT /api/productos/{id} — editar producto
    @PUT("api/productos/{id}")
    suspend fun actualizarProducto(
        @Path("id") id: Long,
        @Body producto: ProductoRequest
    ): Response<Producto>

    // DELETE /api/productos/{id} — eliminar producto
    @DELETE("api/productos/{id}")
    suspend fun eliminarProducto(@Path("id") id: Long): Response<Unit>
}