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
    val apellido: String,
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

data class CategoriaRequest(
    @SerializedName("nombreCat") val nombreCat: String
)

data class MarcaRequest(
    @SerializedName("nombreMarc") val nombreMarc: String
)

data class Empleado(
    @SerializedName("idEmpleado") val id: Int,
    @SerializedName("usuario")    val usuario: String,
    @SerializedName("nombre")     val nombre: String,
    @SerializedName("apellido")   val apellido: String,
    @SerializedName("rol")        val rol: Int  // 1 = admin, 2 = empleado
)

data class EmpleadoRequest(
    @SerializedName("usuario")    val usuario: String,
    @SerializedName("nombre")     val nombre: String,
    @SerializedName("apellido")   val apellido: String,
    @SerializedName("rol")        val rol: Int,
    @SerializedName("contrasena") val contrasena: String
)

// model/Tarjeta.kt
data class Tarjeta(
    val idTarjeta: Long,
    val tipo: String,          // "debito" | "credito"
    val digitos: String,       // "4532123456789012"
    val fechaVence: String,    // "12/27"
    val saldo: Double,
    val saldoMaximo: Double?,   // solo crédito, null en débito
    val codSeguridad: String
)

// model/FacturaRequest.kt
data class FacturaRequest(
    val idTarjeta: Long,
    val detalles: List<DetalleRequest>
)

data class DetalleRequest(
    val idProducto: Long,
    val cantidad: Int,
    val precio_unitario: Double
)

// model/FacturaResponse.kt
data class FacturaResponse(
    val idFactura: Long,
    val subtotal: Double,
    val itbms: Double,
    val total: Double
)
