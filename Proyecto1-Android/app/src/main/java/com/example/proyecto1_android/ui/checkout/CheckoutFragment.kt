package com.example.proyecto1_android.ui.checkout

import android.os.Bundle
import android.text.Editable
import android.text.TextWatcher
import android.view.View
import android.widget.AdapterView
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto1_android.R
import com.example.proyecto1_android.data.db.AppDatabase
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.databinding.FragmentCheckoutBinding
import com.example.proyecto1_android.model.Tarjeta

class CheckoutFragment : Fragment(R.layout.fragment_checkout) {

    private var _binding: FragmentCheckoutBinding? = null
    private val binding get() = _binding!!

    private val viewModel: CheckoutViewModel by viewModels {
        val db = AppDatabase.getInstance(requireContext())
        CheckoutViewModel.Factory(CarritoRepository(db.carritoDao()))
    }

    private var todasLasTarjetas: List<Tarjeta> = emptyList()
    private var tarjetaSeleccionada: Tarjeta? = null

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentCheckoutBinding.bind(view)

        val resumenAdapter = ResumenAdapter()
        binding.rvResumen.layoutManager = LinearLayoutManager(requireContext())
        binding.rvResumen.adapter = resumenAdapter

        // ── Spinner tipo de tarjeta ───────────────────────────────
        val tipos = listOf("Selecciona", "Débito", "Crédito")
        binding.spinnerTipoTarjeta.adapter = ArrayAdapter(
            requireContext(),
            android.R.layout.simple_spinner_dropdown_item,
            tipos
        )
        binding.spinnerTipoTarjeta.onItemSelectedListener = object : AdapterView.OnItemSelectedListener {
            override fun onItemSelected(p: AdapterView<*>?, v: View?, pos: Int, id: Long) {
                if (pos == 0) {
                    tarjetaSeleccionada = null
                    binding.cardInfoTarjeta.visibility = View.GONE
                }
            }
            override fun onNothingSelected(p: AdapterView<*>?) {}
        }

        binding.etNumeroTarjeta.hint = ""

        // ── Auto-formato fecha MM/AA ──────────────────────────────
        binding.etFechaVence.addTextChangedListener(object : TextWatcher {
            private var isFormatting = false
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                if (isFormatting) return
                isFormatting = true
                val digits = s.toString().filter { it.isDigit() }.take(4)
                val formatted = when {
                    digits.length >= 3 -> "${digits.substring(0, 2)}/${digits.substring(2)}"
                    digits.length == 2 -> "$digits/"
                    else               -> digits
                }
                s?.replace(0, s.length, formatted)
                isFormatting = false
            }
        })

        // ── Auto-formato número de tarjeta ────────────────────────
        binding.etNumeroTarjeta.addTextChangedListener(object : TextWatcher {
            private var isFormatting = false
            override fun beforeTextChanged(s: CharSequence?, start: Int, count: Int, after: Int) {}
            override fun onTextChanged(s: CharSequence?, start: Int, before: Int, count: Int) {}
            override fun afterTextChanged(s: Editable?) {
                if (isFormatting) return
                isFormatting = true
                val digits = s.toString().filter { it.isDigit() }.take(16)
                val formatted = digits.chunked(4).joinToString(" ")
                s?.replace(0, s.length, formatted)
                isFormatting = false
            }
        })

        // ── Cargar tarjetas ───────────────────────────────────────
        viewModel.tarjetas.observe(viewLifecycleOwner) { lista ->
            todasLasTarjetas = lista
        }

        // ── Totales ───────────────────────────────────────────────
        viewModel.items.observe(viewLifecycleOwner) { resumenAdapter.submitList(it) }
        viewModel.subtotal.observe(viewLifecycleOwner) { binding.tvSubtotal.text = "$%.2f".format(it) }
        viewModel.itbms.observe(viewLifecycleOwner)    { binding.tvItbms.text    = "$%.2f".format(it) }
        viewModel.total.observe(viewLifecycleOwner) { tot ->
            binding.tvTotal.text = "$%.2f".format(tot)
            binding.etMontoAPagar.setText("$%.2f".format(tot))
        }

        // ── Loading / Error ───────────────────────────────────────
        viewModel.isLoading.observe(viewLifecycleOwner) { loading ->
            binding.progressCheckout.visibility = if (loading) View.VISIBLE else View.GONE
            binding.btnPagar.isEnabled = !loading
        }
        viewModel.error.observe(viewLifecycleOwner) { err ->
            err?.let {
                Toast.makeText(requireContext(), it, Toast.LENGTH_LONG).show()
                viewModel.clearError()
            }
        }

        // ✅ Navegar a pantalla de éxito con datos de la factura
        viewModel.facturaCreada.observe(viewLifecycleOwner) { factura ->
            factura ?: return@observe
            val itemsActuales = viewModel.items.value?.toTypedArray() ?: emptyArray()
            val action = CheckoutFragmentDirections.actionCheckoutToPagoExitoso(
                idFactura = factura.idFactura,
                subtotal  = factura.subtotal.toFloat(),
                itbms     = factura.itbms.toFloat(),
                total     = factura.total.toFloat(),
                items     = itemsActuales
            )
            findNavController().navigate(action)
        }

        // ── Botón pagar ───────────────────────────────────────────
        binding.btnPagar.setOnClickListener {
            val numero = binding.etNumeroTarjeta.text.toString().filter { it.isDigit() }
            val fecha  = binding.etFechaVence.text.toString().trim()
            val cvv    = binding.etCvv.text.toString().trim()

            if (binding.spinnerTipoTarjeta.selectedItemPosition == 0) {
                Toast.makeText(requireContext(), "Selecciona el tipo de tarjeta", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (numero.length < 16) {
                Toast.makeText(requireContext(), "Ingresa el número completo de la tarjeta", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (fecha.length < 5) {
                Toast.makeText(requireContext(), "Ingresa la fecha de vencimiento (MM/AA)", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (cvv.length < 3) {
                Toast.makeText(requireContext(), "Ingresa el código CVV", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }

            val partes = fecha.split("/")
            val fechaBD = if (partes.size == 2) {
                "20${partes[1]}-${partes[0].padStart(2, '0')}"
            } else ""

            val tarjeta = todasLasTarjetas.find { t ->
                t.digitos      == numero &&
                        t.codSeguridad == cvv &&
                        t.fechaVence.startsWith(fechaBD)
            }

            if (tarjeta == null) {
                Toast.makeText(requireContext(), "Los datos de la tarjeta no son válidos", Toast.LENGTH_LONG).show()
                return@setOnClickListener
            }

            tarjetaSeleccionada = tarjeta
            mostrarInfoTarjeta(tarjeta)
            viewModel.pagar(tarjeta.idTarjeta)
        }

        binding.btnVolver.setOnClickListener {
            findNavController().popBackStack()
        }
    }

    private fun mostrarInfoTarjeta(t: Tarjeta) {
        binding.cardInfoTarjeta.visibility = View.VISIBLE
        binding.tvSaldoTarjeta.text = when (t.tipo.lowercase()) {
            "débito", "debito" -> "Saldo disponible: ${"$"}%.2f".format(t.saldo)
            "crédito", "credito" -> {
                val disponible = (t.saldoMaximo ?: 0.0) - t.saldo
                "Crédito disponible: ${"$"}%.2f".format(disponible)
            }
            else -> ""
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}