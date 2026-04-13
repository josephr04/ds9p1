// =====================================================================
// model/Producto.kt
// Equivalente a fetch_object() en tu PHP — mapea el JSON de la API
// =====================================================================
package com.example.proyecto1_android.model

import com.google.gson.annotations.SerializedName

data class Producto(
    @SerializedName("idProducto")   val id: Long,
    @SerializedName("nombre")       val nombre: String,
    @SerializedName("descripcion")  val descripcion: String,
    @SerializedName("precioCosto")  val precioCosto: Double,
    @SerializedName("precioVenta")  val precioVenta: Double,
    @SerializedName("unidad")       val unidad: String,
    @SerializedName("imagen")       val imagen: String,        // nombre del archivo
    @SerializedName("stock")        val stock: Int = 0,
    @SerializedName("idCategoria")  val idCategoria: Int? = null,
    @SerializedName("idMarca")      val idMarca: Int? = null,

    // Relaciones que Laravel puede devolver con ->with()
    @SerializedName("categoria")    val categoria: Categoria? = null,
    @SerializedName("marca")        val marca: Marca? = null
) {
    // URL completa de la imagen — equivalente a tu:
    // 'http://localhost/Proyecto%201/imagenes/' . $datos->imagen
    // 10.0.2.2 = localhost desde el emulador Android
    fun imageUrl(): String = "file:///android_asset/$imagen"
}

// =====================================================================
// model/Categoria.kt
// =====================================================================
data class Categoria(
    @SerializedName("idCategoria")  val id: Int,
    @SerializedName("nombreCat")    val nombre: String
)

// =====================================================================
// model/Marca.kt
// =====================================================================
data class Marca(
    @SerializedName("idMarca")      val id: Int,
    @SerializedName("nombreMarc")   val nombre: String
)