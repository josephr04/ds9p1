// =====================================================================
// ui/admin/AdminItemSimpleAdapter.kt
// Adapter genérico reutilizable para Categorías y Marcas
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto1_android.databinding.ItemAdminSimpleBinding

// Modelo simple genérico
data class ItemSimple(val id: Int, val nombre: String)

class AdminItemSimpleAdapter(
    private val onEditar: (id: Int, nombre: String) -> Unit,
    private val onEliminar: (id: Int, nombre: String) -> Unit
) : ListAdapter<ItemSimple, AdminItemSimpleAdapter.ViewHolder>(DiffCallback()) {

    inner class ViewHolder(private val binding: ItemAdminSimpleBinding)
        : RecyclerView.ViewHolder(binding.root) {

        fun bind(item: ItemSimple) {
            binding.tvNombreItem.text  = item.nombre
            binding.tvIdItem.text     = "ID: ${item.id}"
            binding.btnEditarItem.setOnClickListener  { onEditar(item.id, item.nombre) }
            binding.btnEliminarItem.setOnClickListener { onEliminar(item.id, item.nombre) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemAdminSimpleBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class DiffCallback : DiffUtil.ItemCallback<ItemSimple>() {
        override fun areItemsTheSame(a: ItemSimple, b: ItemSimple) = a.id == b.id
        override fun areContentsTheSame(a: ItemSimple, b: ItemSimple) = a == b
    }
}