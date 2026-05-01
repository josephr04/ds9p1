// =====================================================================
// ui/tienda/TiendaFragment.kt — con barra de búsqueda agregada
// El resto queda igual que el original
// =====================================================================
package com.example.proyecto1_android.ui.tienda

import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
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

        // ── NUEVO: barra de búsqueda ──────────────────────────────
        binding.etBusqueda.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                viewModel.buscar(s?.toString() ?: "")
            }
        })

        // Productos
        viewModel.productos.observe(viewLifecycleOwner) { lista ->
            adapter.submitList(lista)
            binding.tvVacio.visibility = if (lista.isEmpty()) View.VISIBLE else View.GONE
        }

        // Loading
        viewModel.cargando.observe(viewLifecycleOwner) { cargando ->
            binding.progressBar.visibility = if (cargando) View.VISIBLE else View.GONE
        }

        // Errores
        viewModel.error.observe(viewLifecycleOwner) { error ->
            error?.let {
                Toast.makeText(context, "Error: $it", Toast.LENGTH_LONG).show()
            }
        }

        // Chips de categorías — igual que el original
        viewModel.categorias.observe(viewLifecycleOwner) { categorias ->
            binding.chipGroupCategorias.removeAllViews()

            val chipTodos = Chip(requireContext()).apply {
                text = "Todos"
                isCheckable = true
                isChecked = true
                setOnClickListener {
                    // Al cambiar categoría se limpia también la búsqueda
                    binding.etBusqueda.setText("")
                    viewModel.cargarProductos(null)
                }
            }
            binding.chipGroupCategorias.addView(chipTodos)

            categorias.forEach { cat ->
                val chip = Chip(requireContext()).apply {
                    text = cat.nombre
                    isCheckable = true
                    setOnClickListener {
                        binding.etBusqueda.setText("")
                        viewModel.cargarProductos(cat.id)
                    }
                }
                binding.chipGroupCategorias.addView(chip)
            }
        }

        viewModel.cantidadCarrito.observe(viewLifecycleOwner) {
            activity?.invalidateOptionsMenu()
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}