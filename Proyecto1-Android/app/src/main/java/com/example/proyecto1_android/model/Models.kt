// =====================================================================
// model/Models.kt — igual que tu original + ProductoRequest al final
// =====================================================================
package com.example.proyecto1_android.model

import com.google.gson.annotations.SerializedName

data class Producto(
    @SerializedName("idProducto")  val id: Long,
    @SerializedName("nombre")      val nombre: String,
    @SerializedName("descripcion") val descripcion: String,
    @SerializedName("precioCosto") val precioCosto: Double,
    @SerializedName("precioVenta") val precioVenta: Double,
    @SerializedName("unidad")      val unidad: String,
    @SerializedName("imagen")      val imagen: String,
    @SerializedName("stock")       val stock: Int = 0,
    @SerializedName("idCategoria") val idCategoria: Int? = null,
    @SerializedName("idMarca")     val idMarca: Int? = null,
    @SerializedName("categoria")   val categoria: Categoria? = null,
    @SerializedName("marca")       val marca: Marca? = null
) {
    fun imageUrl(): String = "file:///android_asset/$imagen"
}

data class Categoria(
    @SerializedName("idCategoria") val id: Int,
    @SerializedName("nombreCat")   val nombre: String
)

data class Marca(
    @SerializedName("idMarca")    val id: Int,
    @SerializedName("nombreMarc") val nombre: String
)

data class LoginRequest(
    val usuario: String,
    val contrasena: String
)

data class LoginResponse(
    val status: String,
    val usuario: String,
    val nombre: String,
    val rol: Int
)

// ── Solo esto es nuevo — para crear/editar desde el admin ─────────────
data class ProductoRequest(
    @SerializedName("nombre")      val nombre: String,
    @SerializedName("descripcion") val descripcion: String,
    @SerializedName("precioCosto") val precioCosto: Double,
    @SerializedName("precioVenta") val precioVenta: Double,
    @SerializedName("unidad")      val unidad: String,
    @SerializedName("imagen")      val imagen: String?,
    @SerializedName("stock")       val stock: Int,
    @SerializedName("idCategoria") val idCategoria: Int,
    @SerializedName("idMarca")     val idMarca: Int
)