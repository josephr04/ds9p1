package com.example.proyecto1_android.ui.tienda

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import com.google.android.material.chip.Chip
import com.example.proyecto1_android.R
import com.example.proyecto1_android.data.db.AppDatabase
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.data.repository.ProductoRepository
import com.example.proyecto1_android.databinding.FragmentTiendaBinding

class TiendaFragment : Fragment(R.layout.fragment_tienda) {

    private var _binding: FragmentTiendaBinding? = null
    private val binding get() = _binding!!

    private val viewModel: TiendaViewModel by viewModels {
        val db = AppDatabase.getInstance(requireContext())
        TiendaViewModel.Factory(
            ProductoRepository(),
            CarritoRepository(db.carritoDao())
        )
    }

    private val adapter = ProductoAdapter(
        onProductoClick = { producto ->
            // Navegar a Detalle pasando el ID — equivalente a href="detalle.php?id=..."
            val action = TiendaFragmentDirections
                .actionTiendaToDetalle(producto.id)
            findNavController().navigate(action)
        },
        onAgregarClick = { producto ->
            viewModel.agregarAlCarrito(producto)
            Toast.makeText(context, "✓ ${producto.nombre} agregado", Toast.LENGTH_SHORT).show()
        }
    )

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentTiendaBinding.bind(view)

        binding.rvProductos.adapter = adapter

        // Observar productos — equivalente al while(fetch_object())
        viewModel.productos.observe(viewLifecycleOwner) { lista ->
            adapter.submitList(lista)
            binding.tvVacio.visibility = if (lista.isEmpty()) View.VISIBLE else View.GONE
        }

        // Indicador de carga
        viewModel.cargando.observe(viewLifecycleOwner) { cargando ->
            binding.progressBar.visibility = if (cargando) View.VISIBLE else View.GONE
        }

        // Errores como Snackbar
        viewModel.error.observe(viewLifecycleOwner) { error ->
            error?.let {
                Toast.makeText(context, "Error: $it", Toast.LENGTH_LONG).show()
            }
        }

        // Chips de categorías — equivalente al sidebar de tienda.php
        viewModel.categorias.observe(viewLifecycleOwner) { categorias ->
            binding.chipGroupCategorias.removeAllViews()

            // Chip "Todos"
            val chipTodos = Chip(requireContext()).apply {
                text = "Todos"
                isCheckable = true
                isChecked = true
                setOnClickListener { viewModel.cargarProductos(null) }
            }
            binding.chipGroupCategorias.addView(chipTodos)

            // Un chip por categoría
            categorias.forEach { cat ->
                val chip = Chip(requireContext()).apply {
                    text = cat.nombre
                    isCheckable = true
                    setOnClickListener { viewModel.cargarProductos(cat.id) }
                }
                binding.chipGroupCategorias.addView(chip)
            }
        }

        // Badge del carrito en el toolbar
        viewModel.cantidadCarrito.observe(viewLifecycleOwner) { cantidad ->
            // Actualiza el badge en el ícono del carrito
            activity?.invalidateOptionsMenu()
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}