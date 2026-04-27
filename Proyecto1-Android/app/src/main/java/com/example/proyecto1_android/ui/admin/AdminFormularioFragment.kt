// =====================================================================
// ui/admin/AdminFormularioFragment.kt — CORREGIDO
// Campos: nombre, descripcion, precioCosto, precioVenta, unidad,
//         imagen, stock, idCategoria, idMarca
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import androidx.navigation.fragment.navArgs
import com.example.proyecto1_android.databinding.FragmentAdminFormularioBinding
import com.example.proyecto1_android.model.ProductoRequest

class AdminFormularioFragment : Fragment() {

    private var _binding: FragmentAdminFormularioBinding? = null
    private val binding get() = _binding!!
    private val viewModel: AdminViewModel by viewModels()
    private val args: AdminFormularioFragmentArgs by navArgs()

    private val esEdicion get() = args.productoId != -1L

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAdminFormularioBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.tvTituloFormulario.text =
            if (esEdicion) "Editar producto" else "Nuevo producto"
        binding.btnGuardar.text =
            if (esEdicion) "Guardar cambios" else "Crear producto"

        if (esEdicion) {
            // ✅ Observa el producto individual
            viewModel.productoSeleccionado.observe(viewLifecycleOwner) { p ->
                p?.let {
                    binding.etNombre.setText(it.nombre)
                    binding.etDescripcion.setText(it.descripcion)
                    binding.etPrecioCosto.setText(it.precioCosto.toString())
                    binding.etPrecioVenta.setText(it.precioVenta.toString())
                    binding.etUnidad.setText(it.unidad)
                    binding.etImagen.setText(it.imagen)
                    binding.etStock.setText(it.stock.toString())
                    binding.etIdCategoria.setText(it.idCategoria?.toString() ?: "")
                    binding.etIdMarca.setText(it.idMarca?.toString() ?: "")
                }
            }
            viewModel.cargarProducto(args.productoId) // ← Solo trae 1
        }

        binding.btnGuardar.setOnClickListener {
            if (validarCampos()) guardar()
        }

        binding.btnCancelar.setOnClickListener {
            findNavController().popBackStack()
        }

        viewModel.operacionExitosa.observe(viewLifecycleOwner) { exitoso ->
            if (exitoso == true) {
                Toast.makeText(
                    requireContext(),
                    if (esEdicion) "Producto actualizado" else "Producto creado",
                    Toast.LENGTH_SHORT
                ).show()
                viewModel.resetOperacion()
                findNavController().popBackStack()
            }
        }

        viewModel.error.observe(viewLifecycleOwner) { error ->
            error?.let {
                Toast.makeText(requireContext(), it, Toast.LENGTH_LONG).show()
                viewModel.clearError()
            }
        }

        viewModel.isLoading.observe(viewLifecycleOwner) { loading ->
            binding.btnGuardar.isEnabled = !loading
            binding.progressBarFormulario.visibility =
                if (loading) View.VISIBLE else View.GONE
        }
    }

    private fun validarCampos(): Boolean {
        var ok = true
        fun check(text: String?, layout: com.google.android.material.textfield.TextInputLayout, msg: String) {
            if (text.isNullOrBlank()) { layout.error = msg; ok = false }
            else layout.error = null
        }
        check(binding.etNombre.text?.toString(),      binding.tilNombre,      "Requerido")
        check(binding.etPrecioCosto.text?.toString(), binding.tilPrecioCosto, "Requerido")
        check(binding.etPrecioVenta.text?.toString(), binding.tilPrecioVenta, "Requerido")
        check(binding.etUnidad.text?.toString(),      binding.tilUnidad,      "Requerido")
        check(binding.etStock.text?.toString(),       binding.tilStock,       "Requerido")
        return ok
    }

    private fun guardar() {
        val request = ProductoRequest(
            nombre      = binding.etNombre.text.toString().trim(),
            descripcion = binding.etDescripcion.text.toString().trim(),
            precioCosto = binding.etPrecioCosto.text.toString().toDoubleOrNull() ?: 0.0,
            precioVenta = binding.etPrecioVenta.text.toString().toDoubleOrNull() ?: 0.0,
            unidad      = binding.etUnidad.text.toString().trim(),
            imagen      = binding.etImagen.text.toString().trim().ifBlank { null },
            stock       = binding.etStock.text.toString().toIntOrNull() ?: 0,
            idCategoria = binding.etIdCategoria.text.toString().toIntOrNull() ?: 0,
            idMarca     = binding.etIdMarca.text.toString().toIntOrNull() ?: 0
        )
        if (esEdicion) viewModel.actualizarProducto(args.productoId, request)
        else viewModel.crearProducto(request)
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}