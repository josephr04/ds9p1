// =====================================================================
// ui/tienda/ProductoAdapter.kt
// Adapter para el RecyclerView del catálogo
// Equivalente al while($p = $productos->fetch_object()) en tienda.php
// =====================================================================
package com.example.proyecto1_android.ui.tienda

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.example.proyecto1_android.databinding.ItemProductoBinding
import com.example.proyecto1_android.model.Producto

class ProductoAdapter(
    private val onProductoClick: (Producto) -> Unit,        // → navegar a Detalle
    private val onAgregarClick: (Producto) -> Unit          // → agregar al carrito
) : ListAdapter<Producto, ProductoAdapter.ViewHolder>(DiffCallback()) {

    inner class ViewHolder(private val binding: ItemProductoBinding) :
        RecyclerView.ViewHolder(binding.root) {

        fun bind(producto: Producto) {
            binding.tvNombre.text     = producto.nombre
            binding.tvPrecio.text     = "$%.2f".format(producto.precioVenta)
            binding.tvCategoria.text  = producto.categoria?.nombre ?: ""

            // Cargar imagen con Glide — equivalente a <img src="imagenes/...">
            Glide.with(binding.ivProducto)
                .load("file:///android_asset/${producto.imagen}")
                .placeholder(android.R.drawable.ic_menu_gallery)
                .centerCrop()
                .into(binding.ivProducto)

            binding.root.setOnClickListener    { onProductoClick(producto) }
            binding.btnAgregar.setOnClickListener { onAgregarClick(producto) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemProductoBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    private class DiffCallback : DiffUtil.ItemCallback<Producto>() {
        override fun areItemsTheSame(old: Producto, new: Producto) = old.id == new.id
        override fun areContentsTheSame(old: Producto, new: Producto) = old == new
    }
}