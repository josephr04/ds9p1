// =====================================================================
// data/api/ApiService.kt — REEMPLAZA el tuyo
// =====================================================================
package com.example.proyecto1_android.data.api

import com.example.proyecto1_android.model.Categoria
import com.example.proyecto1_android.model.CategoriaRequest
import com.example.proyecto1_android.model.Empleado
import com.example.proyecto1_android.model.EmpleadoRequest
import com.example.proyecto1_android.model.FacturaRequest
import com.example.proyecto1_android.model.FacturaResponse
import com.example.proyecto1_android.model.LoginResponse
import com.example.proyecto1_android.model.Marca
import com.example.proyecto1_android.model.MarcaRequest
import com.example.proyecto1_android.model.Producto
import com.example.proyecto1_android.model.ProductoRequest
import com.example.proyecto1_android.model.Tarjeta
import retrofit2.Response
import retrofit2.http.*

interface ApiService {

    // ── Productos ─────────────────────────────────────────────────
    @GET("api/productos")
    suspend fun getProductos(): Response<List<Producto>>

    @GET("api/productos/{id}")
    suspend fun getProducto(@Path("id") id: Long): Response<Producto>

    @GET("api/productos")
    suspend fun getProductosPorCategoria(
        @Query("categoria_id") categoriaId: Int
    ): Response<List<Producto>>

    @POST("api/productos")
    suspend fun crearProducto(@Body producto: ProductoRequest): Response<Producto>

    @PUT("api/productos/{id}")
    suspend fun actualizarProducto(
        @Path("id") id: Long,
        @Body producto: ProductoRequest
    ): Response<Producto>

    @DELETE("api/productos/{id}")
    suspend fun eliminarProducto(@Path("id") id: Long): Response<Unit>

    // ── Categorías ────────────────────────────────────────────────
    @GET("api/categorias")
    suspend fun getCategorias(): Response<List<Categoria>>

    @POST("api/categorias")
    suspend fun crearCategoria(@Body categoria: CategoriaRequest): Response<Categoria>

    @PUT("api/categorias/{id}")
    suspend fun actualizarCategoria(
        @Path("id") id: Int,
        @Body categoria: CategoriaRequest
    ): Response<Categoria>

    @DELETE("api/categorias/{id}")
    suspend fun eliminarCategoria(@Path("id") id: Int): Response<Unit>

    // ── Marcas ────────────────────────────────────────────────────
    @GET("api/marcas")
    suspend fun getMarcas(): Response<List<Marca>>

    @POST("api/marcas")
    suspend fun crearMarca(@Body marca: MarcaRequest): Response<Marca>

    @PUT("api/marcas/{id}")
    suspend fun actualizarMarca(
        @Path("id") id: Int,
        @Body marca: MarcaRequest
    ): Response<Marca>

    @DELETE("api/marcas/{id}")
    suspend fun eliminarMarca(@Path("id") id: Int): Response<Unit>

    // ── Empleados ─────────────────────────────────────────────────
    @GET("api/empleados")
    suspend fun getEmpleados(): Response<List<Empleado>>

    @POST("api/empleados")
    suspend fun crearEmpleado(@Body empleado: EmpleadoRequest): Response<Empleado>

    @PUT("api/empleados/{id}")
    suspend fun actualizarEmpleado(
        @Path("id") id: Int,
        @Body empleado: EmpleadoRequest
    ): Response<Empleado>

    @DELETE("api/empleados/{id}")
    suspend fun eliminarEmpleado(@Path("id") id: Int): Response<Unit>

    // ── Login ─────────────────────────────────────────────────────
    @FormUrlEncoded
    @POST("api/login")
    suspend fun login(
        @Field("usuario") usuario: String,
        @Field("contrasena") contrasena: String
    ): Response<LoginResponse>

    // Agrega esto a tu ApiService / RetrofitClient interface
    @GET("api/tarjetas")
    suspend fun getTarjetas(): Response<List<Tarjeta>>

    @POST("api/facturas")
    suspend fun crearFactura(@Body request: FacturaRequest): Response<FacturaResponse>
}