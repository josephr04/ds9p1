package com.example.proyecto1_android.ui.checkout

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.example.proyecto1_android.databinding.ItemResumenBinding
import com.example.proyecto1_android.model.CarritoItem

class ResumenAdapter : ListAdapter<CarritoItem, ResumenAdapter.VH>(Diff()) {

    inner class VH(val b: ItemResumenBinding) : RecyclerView.ViewHolder(b.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int) =
        VH(ItemResumenBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: VH, position: Int) {
        val item = getItem(position)
        holder.b.tvNombreResumen.text    = item.nombre
        holder.b.tvCantidadResumen.text  = "x${item.cantidad}"
        holder.b.tvSubtotalResumen.text  = "$%.2f".format(item.subtotal())
    }

    class Diff : DiffUtil.ItemCallback<CarritoItem>() {
        override fun areItemsTheSame(a: CarritoItem, b: CarritoItem) = a.idProducto == b.idProducto
        override fun areContentsTheSame(a: CarritoItem, b: CarritoItem) = a == b
    }
}