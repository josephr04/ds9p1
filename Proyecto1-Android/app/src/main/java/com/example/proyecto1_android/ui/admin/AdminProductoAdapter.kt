// =====================================================================
// ui/admin/AdminProductoAdapter.kt — CORREGIDO
// Usa producto.id (no idProducto), categoria.nombre, marca.nombre
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.example.proyecto1_android.databinding.ItemAdminProductoBinding
import com.example.proyecto1_android.model.Producto

class AdminProductoAdapter(
    private val onEditar: (Producto) -> Unit,
    private val onEliminar: (Producto) -> Unit
) : ListAdapter<Producto, AdminProductoAdapter.ViewHolder>(DiffCallback()) {

    // Ajusta esta ruta a donde estén tus imágenes en el servidor
    private val IMAGE_BASE = "http://10.0.2.2/ds9p1/Proyecto1/imagenes/"

    inner class ViewHolder(private val binding: ItemAdminProductoBinding)
        : RecyclerView.ViewHolder(binding.root) {

        fun bind(producto: Producto) {
            binding.tvNombreAdmin.text    = producto.nombre
            binding.tvPrecioAdmin.text    = "$${"%.2f".format(producto.precioCosto)}"
            binding.tvStockAdmin.text     = "Stock: ${producto.stock} | ${producto.unidad}"
            binding.tvCategoriaAdmin.text = producto.categoria?.nombre ?: ""

            Glide.with(binding.root)
                .load("$IMAGE_BASE${producto.imagen}")
                .centerCrop()
                .into(binding.ivProductoAdmin)

            binding.btnEditar.setOnClickListener { onEditar(producto) }
            binding.btnEliminar.setOnClickListener { onEliminar(producto) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemAdminProductoBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class DiffCallback : DiffUtil.ItemCallback<Producto>() {
        // Usa .id (que mapea a idProducto via @SerializedName)
        override fun areItemsTheSame(a: Producto, b: Producto) = a.id == b.id
        override fun areContentsTheSame(a: Producto, b: Producto) = a == b
    }
}