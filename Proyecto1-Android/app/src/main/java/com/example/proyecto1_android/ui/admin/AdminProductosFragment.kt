// =====================================================================
// ui/admin/AdminProductosFragment.kt — con búsqueda y chips de categoría
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto1_android.databinding.FragmentAdminProductosBinding
import com.example.proyecto1_android.model.Categoria
import com.google.android.material.chip.Chip

class AdminProductosFragment : Fragment() {

    private var _binding: FragmentAdminProductosBinding? = null
    private val binding get() = _binding!!
    private val viewModel: AdminViewModel by viewModels()
    private lateinit var adapter: AdminProductoAdapter

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAdminProductosBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        setupAdapter()
        setupSearchBar()
        setupObservers()

        viewModel.cargarProductos()
        viewModel.cargarCategorias()
    }

    private fun setupAdapter() {
        adapter = AdminProductoAdapter(
            onEditar = { producto ->
                val action = AdminProductosFragmentDirections
                    .actionAdminProductosToAdminFormulario(producto.id)
                findNavController().navigate(action)
            },
            onEliminar = { producto ->
                AlertDialog.Builder(requireContext())
                    .setTitle("Eliminar producto")
                    .setMessage("¿Eliminar \"${producto.nombre}\"?")
                    .setPositiveButton("Eliminar") { _, _ ->
                        viewModel.eliminarProducto(producto.id)
                    }
                    .setNegativeButton("Cancelar", null)
                    .show()
            }
        )
        binding.rvAdminProductos.layoutManager = LinearLayoutManager(requireContext())
        binding.rvAdminProductos.adapter = adapter

        binding.fabAgregarProducto.setOnClickListener {
            val action = AdminProductosFragmentDirections
                .actionAdminProductosToAdminFormulario(-1L)
            findNavController().navigate(action)
        }
    }

    private fun setupSearchBar() {
        binding.etBusqueda.addTextChangedListener(object : TextWatcher {
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                viewModel.buscar(s?.toString() ?: "")
            }
        })
    }

    private fun setupObservers() {
        // Lista de productos (ya filtrada por el ViewModel)
        viewModel.productos.observe(viewLifecycleOwner) { productos ->
            adapter.submitList(productos)
            binding.tvVacioAdmin.visibility =
                if (productos.isEmpty()) View.VISIBLE else View.GONE
        }

        // Chips de categorías
        viewModel.categorias.observe(viewLifecycleOwner) { categorias ->
            construirChips(categorias)
        }

        viewModel.isLoading.observe(viewLifecycleOwner) { loading ->
            binding.progressBarAdmin.visibility =
                if (loading) View.VISIBLE else View.GONE
        }

        viewModel.error.observe(viewLifecycleOwner) { error ->
            error?.let {
                Toast.makeText(requireContext(), it, Toast.LENGTH_LONG).show()
                viewModel.clearError()
            }
        }

        viewModel.operacionExitosa.observe(viewLifecycleOwner) { exitoso ->
            if (exitoso == true) {
                Toast.makeText(requireContext(), "Producto eliminado", Toast.LENGTH_SHORT).show()
                viewModel.cargarProductos()
                viewModel.resetOperacion()
            }
        }
    }

    private fun construirChips(categorias: List<Categoria>) {
        val chipGroup = binding.chipGroupCategorias
        chipGroup.removeAllViews()

        // Chip "Todos"
        val chipTodos = Chip(requireContext()).apply {
            text = "Todos"
            isCheckable = true
            isChecked = true
        }
        chipTodos.setOnClickListener {
            chipGroup.clearCheck()
            chipTodos.isChecked = true
            viewModel.filtrarPorCategoria(null)
        }
        chipGroup.addView(chipTodos)

        // Un chip por cada categoría
        categorias.forEach { categoria ->
            val chip = Chip(requireContext()).apply {
                text = categoria.nombre
                isCheckable = true
                tag = categoria.id
            }
            chip.setOnClickListener {
                chipGroup.clearCheck()
                chip.isChecked = true
                viewModel.filtrarPorCategoria(categoria.id)
            }
            chipGroup.addView(chip)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}