// =====================================================================
// ui/admin/AdminEmpleadosFragment.kt
// Lista empleados con CRUD completo
// Formulario de crear/editar en un AlertDialog con todos los campos
// =====================================================================
package com.example.proyecto1_android.ui.admin

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.*
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto1_android.databinding.FragmentAdminEmpleadosBinding
import com.example.proyecto1_android.model.Empleado
import com.example.proyecto1_android.model.EmpleadoRequest

class AdminEmpleadosFragment : Fragment() {

    private var _binding: FragmentAdminEmpleadosBinding? = null
    private val binding get() = _binding!!
    private val viewModel: AdminEmpleadosViewModel by viewModels()

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAdminEmpleadosBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        val adapter = AdminEmpleadoAdapter(
            onEditar  = { empleado -> mostrarDialogoFormulario(empleado) },
            onEliminar = { empleado ->
                AlertDialog.Builder(requireContext())
                    .setTitle("Eliminar empleado")
                    .setMessage("¿Eliminar a \"${empleado.nombre} ${empleado.apellido}\"?")
                    .setPositiveButton("Eliminar") { _, _ -> viewModel.eliminar(empleado.id) }
                    .setNegativeButton("Cancelar", null)
                    .show()
            }
        )

        binding.rvEmpleados.layoutManager = LinearLayoutManager(requireContext())
        binding.rvEmpleados.adapter = adapter

        binding.fabAgregarEmpleado.setOnClickListener {
            mostrarDialogoFormulario(null) // null = crear nuevo
        }

        viewModel.empleados.observe(viewLifecycleOwner) { lista ->
            adapter.submitList(lista)
            binding.tvVacioEmpleados.visibility =
                if (lista.isEmpty()) View.VISIBLE else View.GONE
        }

        viewModel.isLoading.observe(viewLifecycleOwner) { loading ->
            binding.progressBarEmpleados.visibility =
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

    // ── Formulario crear/editar en AlertDialog ────────────────────
    private fun mostrarDialogoFormulario(empleado: Empleado?) {
        val esEdicion = empleado != null

        val layout = LinearLayout(requireContext()).apply {
            orientation = LinearLayout.VERTICAL
            setPadding(48, 24, 48, 8)
        }

        // Función helper que crea un TextInputLayout con label visible
        fun campo(label: String, valor: String = "", tipo: Int = android.text.InputType.TYPE_CLASS_TEXT): Pair<com.google.android.material.textfield.TextInputLayout, com.google.android.material.textfield.TextInputEditText> {
            val til = com.google.android.material.textfield.TextInputLayout(
                requireContext(),
                null,
                com.google.android.material.R.attr.textInputOutlinedStyle
            ).apply {
                hint = label
                layoutParams = LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT,
                    LinearLayout.LayoutParams.WRAP_CONTENT
                ).apply { bottomMargin = 24 }
            }

            val et = com.google.android.material.textfield.TextInputEditText(til.context).apply {
                setText(valor)
                inputType = tipo
            }

            til.addView(et)
            return Pair(til, et)
        }

        val (tilUsuario,   etUsuario)   = campo("Usuario",   empleado?.usuario  ?: "")
        val (tilNombre,    etNombre)    = campo("Nombre",    empleado?.nombre   ?: "")
        val (tilApellido,  etApellido)  = campo("Apellido",  empleado?.apellido ?: "")
        val (tilContrasena, etContrasena) = campo(
            if (esEdicion) "Nueva contraseña (dejar vacío para no cambiar)" else "Contraseña",
            tipo = android.text.InputType.TYPE_CLASS_TEXT or
                    android.text.InputType.TYPE_TEXT_VARIATION_PASSWORD
        )
        // Icono para mostrar/ocultar contraseña (opcional pero bonito)
        tilContrasena.endIconMode = com.google.android.material.textfield.TextInputLayout.END_ICON_PASSWORD_TOGGLE

        val tvRol = TextView(requireContext()).apply {
            text = "Rol"
            textSize = 12f
            setTextColor(resources.getColor(android.R.color.darker_gray, null))
            layoutParams = LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
            ).apply { bottomMargin = 4 }
        }

        val spinnerRol = Spinner(requireContext()).apply {
            adapter = ArrayAdapter(
                requireContext(),
                android.R.layout.simple_spinner_dropdown_item,
                listOf("Administrador (1)", "Empleado (2)")
            )
            setSelection(if ((empleado?.rol ?: 2) == 1) 0 else 1)
            layoutParams = LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
            ).apply { bottomMargin = 16 }
        }

        layout.addView(tilUsuario)
        layout.addView(tilNombre)
        layout.addView(tilApellido)
        layout.addView(tilContrasena)
        layout.addView(tvRol)
        layout.addView(spinnerRol)

        // ... resto del AlertDialog igual que antes, pero usando etUsuario, etNombre, etc.
        AlertDialog.Builder(requireContext())
            .setTitle(if (esEdicion) "Editar empleado" else "Nuevo empleado")
            .setView(layout)
            .setPositiveButton(if (esEdicion) "Guardar" else "Crear") { _, _ ->
                val usuario    = etUsuario.text.toString().trim()
                val nombre     = etNombre.text.toString().trim()
                val apellido   = etApellido.text.toString().trim()
                val contrasena = etContrasena.text.toString().trim()
                val rol = if (spinnerRol.selectedItemPosition == 0) 1 else 2

                if (usuario.isEmpty() || nombre.isEmpty() || apellido.isEmpty()) {
                    Toast.makeText(requireContext(), "Usuario, nombre y apellido son requeridos", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }
                if (!esEdicion && contrasena.isEmpty()) {
                    Toast.makeText(requireContext(), "La contraseña es requerida", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }
                if (!esEdicion && contrasena.length < 6) {
                    Toast.makeText(requireContext(), "La contraseña debe tener mínimo 6 caracteres", Toast.LENGTH_SHORT).show()
                    return@setPositiveButton
                }

                val request = EmpleadoRequest(
                    usuario    = usuario,
                    nombre     = nombre,
                    apellido   = apellido,
                    rol        = rol,
                    contrasena = contrasena.ifEmpty { "" }
                )

                if (esEdicion) viewModel.editar(empleado!!.id, request)
                else viewModel.crear(request)
            }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}