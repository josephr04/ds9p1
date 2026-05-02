// =====================================================================
// ui/admin/AdminMarcasFragment.kt
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.EditText
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto1_android.databinding.FragmentAdminListaSimpleBinding

class AdminMarcasFragment : Fragment() {

    private var _binding: FragmentAdminListaSimpleBinding? = null
    private val binding get() = _binding!!
    private val viewModel: AdminMarcasViewModel by viewModels()

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAdminListaSimpleBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.tvTituloLista.text = "Marcas"

        val adapter = AdminItemSimpleAdapter(
            onEditar = { id, nombre -> mostrarDialogoEditar(id, nombre) },
            onEliminar = { id, nombre ->
                AlertDialog.Builder(requireContext())
                    .setTitle("Eliminar marca")
                    .setMessage("¿Eliminar \"$nombre\"?")
                    .setPositiveButton("Eliminar") { _, _ -> viewModel.eliminar(id) }
                    .setNegativeButton("Cancelar", null)
                    .show()
            }
        )

        binding.rvListaSimple.layoutManager = LinearLayoutManager(requireContext())
        binding.rvListaSimple.adapter = adapter

        binding.fabAgregarItem.setOnClickListener { mostrarDialogoCrear() }

        viewModel.marcas.observe(viewLifecycleOwner) { lista ->
            adapter.submitList(lista.map { ItemSimple(it.id, it.nombre) })
            binding.tvVacioLista.visibility =
                if (lista.isEmpty()) View.VISIBLE else View.GONE
        }

        viewModel.isLoading.observe(viewLifecycleOwner) { loading ->
            binding.progressBarLista.visibility =
                if (loading) View.VISIBLE else View.GONE
        }

        viewModel.operacionExitosa.observe(viewLifecycleOwner) { msg ->
            msg?.let {
                Toast.makeText(requireContext(), it, Toast.LENGTH_SHORT).show()
                viewModel.clearMensaje()
            }
        }

        viewModel.error.observe(viewLifecycleOwner) { err ->
            err?.let {
                Toast.makeText(requireContext(), it, Toast.LENGTH_LONG).show()
                viewModel.clearError()
            }
        }

        viewModel.cargar()
    }

    private fun mostrarDialogoCrear() {
        val input = EditText(requireContext()).apply {
            hint = "Nombre de la marca"
            setPadding(48, 24, 48, 24)
        }
        AlertDialog.Builder(requireContext())
            .setTitle("Nueva marca")
            .setView(input)
            .setPositiveButton("Crear") { _, _ ->
                val nombre = input.text.toString().trim()
                if (nombre.isNotEmpty()) viewModel.crear(nombre)
                else Toast.makeText(requireContext(), "Ingresa un nombre", Toast.LENGTH_SHORT).show()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun mostrarDialogoEditar(id: Int, nombreActual: String) {
        val input = EditText(requireContext()).apply {
            setText(nombreActual)
            setPadding(48, 24, 48, 24)
        }
        AlertDialog.Builder(requireContext())
            .setTitle("Editar marca")
            .setView(input)
            .setPositiveButton("Guardar") { _, _ ->
                val nombre = input.text.toString().trim()
                if (nombre.isNotEmpty()) viewModel.editar(id, nombre)
                else Toast.makeText(requireContext(), "Ingresa un nombre", Toast.LENGTH_SHORT).show()
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}