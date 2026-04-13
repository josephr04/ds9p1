package com.example.proyecto1_android.ui.carrito

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.example.proyecto1_android.databinding.ItemCarritoBinding
import com.example.proyecto1_android.model.CarritoItem

class CarritoAdapter(
    private val onSumar: (CarritoItem) -> Unit,
    private val onRestar: (CarritoItem) -> Unit,
    private val onEliminar: (CarritoItem) -> Unit
) : ListAdapter<CarritoItem, CarritoAdapter.ViewHolder>(DiffCallback()) {

    inner class ViewHolder(private val binding: ItemCarritoBinding) :
        RecyclerView.ViewHolder(binding.root) {

        fun bind(item: CarritoItem) {
            binding.tvNombre.text    = item.nombre
            binding.tvPrecio.text    = "$%.2f".format(item.precioVenta)
            binding.tvCantidad.text  = item.cantidad.toString()
            binding.tvSubtotal.text  = "$%.2f".format(item.subtotal())

            Glide.with(binding.ivProducto)
                .load("file:///android_asset/${item.imagen}")
                .centerCrop()
                .into(binding.ivProducto)

            binding.btnSumar.setOnClickListener   { onSumar(item) }
            binding.btnRestar.setOnClickListener  { onRestar(item) }
            binding.btnEliminar.setOnClickListener { onEliminar(item) }
        }
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder {
        val binding = ItemCarritoBinding.inflate(
            LayoutInflater.from(parent.context), parent, false
        )
        return ViewHolder(binding)
    }

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    private class DiffCallback : DiffUtil.ItemCallback<CarritoItem>() {
        override fun areItemsTheSame(old: CarritoItem, new: CarritoItem) =
            old.idProducto == new.idProducto
        override fun areContentsTheSame(old: CarritoItem, new: CarritoItem) = old == new
    }
}