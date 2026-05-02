// =====================================================================
// ui/admin/AdminEmpleadoAdapter.kt
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto1_android.databinding.ItemAdminEmpleadoBinding
import com.example.proyecto1_android.model.Empleado

class AdminEmpleadoAdapter(
    private val onEditar: (Empleado) -> Unit,
    private val onEliminar: (Empleado) -> Unit
) : ListAdapter<Empleado, AdminEmpleadoAdapter.ViewHolder>(DiffCallback()) {

    inner class ViewHolder(private val binding: ItemAdminEmpleadoBinding)
        : RecyclerView.ViewHolder(binding.root) {

        fun bind(empleado: Empleado) {
            binding.tvNombreEmpleado.text   = "${empleado.nombre} ${empleado.apellido}"
            binding.tvUsuarioEmpleado.text  = "@${empleado.usuario}"
            binding.tvRolEmpleado.text      = if (empleado.rol == 1) "Administrador" else "Empleado"

            // Color del badge de rol
            val colorRol = if (empleado.rol == 1) 0xFF4F46E5.toInt() else 0xFF64748B.toInt()
            binding.tvRolEmpleado.setBackgroundColor(colorRol)

            binding.btnEditarEmpleado.setOnClickListener  { onEditar(empleado) }
            binding.btnEliminarEmpleado.setOnClickListener { onEliminar(empleado) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemAdminEmpleadoBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    class DiffCallback : DiffUtil.ItemCallback<Empleado>() {
        override fun areItemsTheSame(a: Empleado, b: Empleado) = a.id == b.id
        override fun areContentsTheSame(a: Empleado, b: Empleado) = a == b
    }
}