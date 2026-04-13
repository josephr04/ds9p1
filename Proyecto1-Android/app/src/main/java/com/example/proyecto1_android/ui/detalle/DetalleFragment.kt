package com.example.proyecto1_android.ui.detalle

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.navArgs
import com.bumptech.glide.Glide
import com.google.android.material.snackbar.Snackbar
import com.example.proyecto1_android.R
import com.example.proyecto1_android.data.db.AppDatabase
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.data.repository.ProductoRepository
import com.example.proyecto1_android.databinding.FragmentDetalleBinding

class DetalleFragment : Fragment(R.layout.fragment_detalle) {

    private var _binding: FragmentDetalleBinding? = null
    private val binding get() = _binding!!

    // Args de navegación — equivalente a $_GET['id'] en detalle.php
    private val args: DetalleFragmentArgs by navArgs()

    private var cantidad = 1

    private val viewModel: DetalleViewModel by viewModels {
        val db = AppDatabase.getInstance(requireContext())
        DetalleViewModel.Factory(
            ProductoRepository(),
            CarritoRepository(db.carritoDao())
        )
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentDetalleBinding.bind(view)

        // Cargar producto — equivalente al query de detalle.php
        viewModel.cargarProducto(args.idProducto)

        viewModel.producto.observe(viewLifecycleOwner) { producto ->
            producto ?: return@observe

            binding.tvNombre.text      = producto.nombre
            binding.tvCategoria.text   = producto.categoria?.nombre ?: ""
            binding.tvPrecio.text      = "$%.2f".format(producto.precioVenta)
            binding.tvDescripcion.text = producto.descripcion

            Glide.with(binding.ivProducto)
                .load("file:///android_asset/${producto.imagen}")
                .centerCrop()
                .into(binding.ivProducto)
        }

        viewModel.cargando.observe(viewLifecycleOwner) { cargando ->
            binding.progressBar.visibility = if (cargando) View.VISIBLE else View.GONE
        }

        binding.toolbar.setNavigationOnClickListener {
            requireActivity().onBackPressedDispatcher.onBackPressed()
        }

        // Botones de cantidad — equivalente a updateQty() en detalle.php
        binding.btnMenos.setOnClickListener {
            if (cantidad > 1) {
                cantidad--
                binding.tvCantidad.text = cantidad.toString()
            }
        }
        binding.btnMas.setOnClickListener {
            cantidad++
            binding.tvCantidad.text = cantidad.toString()
        }

        // Agregar al carrito — equivalente al fetch de carritoController
        binding.btnAgregarCarrito.setOnClickListener {
            viewModel.agregarAlCarrito(cantidad)
        }

        // Feedback de éxito — equivalente al toast de tienda.php
        viewModel.agregado.observe(viewLifecycleOwner) { agregado ->
            if (agregado) {
                Snackbar.make(
                    binding.root,
                    "✓ Producto agregado al carrito",
                    Snackbar.LENGTH_SHORT
                ).show()
            }
        }

        viewModel.error.observe(viewLifecycleOwner) { error ->
            error?.let { Toast.makeText(context, it, Toast.LENGTH_LONG).show() }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}