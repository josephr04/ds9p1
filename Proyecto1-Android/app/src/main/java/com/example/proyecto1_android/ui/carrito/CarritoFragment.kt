package com.example.proyecto1_android.ui.carrito

import android.os.Bundle
import android.view.View
import androidx.fragment.app.Fragment
import androidx.fragment.app.viewModels
import androidx.navigation.fragment.findNavController
import com.example.proyecto1_android.R
import com.example.proyecto1_android.data.db.AppDatabase
import com.example.proyecto1_android.data.repository.CarritoRepository
import com.example.proyecto1_android.databinding.FragmentCarritoBinding
import androidx.recyclerview.widget.LinearLayoutManager

class CarritoFragment : Fragment(R.layout.fragment_carrito) {

    private var _binding: FragmentCarritoBinding? = null
    private val binding get() = _binding!!

    private val viewModel: CarritoViewModel by viewModels {
        val db = AppDatabase.getInstance(requireContext())
        CarritoViewModel.Factory(CarritoRepository(db.carritoDao()))
    }

    private val adapter = CarritoAdapter(
        onSumar    = { viewModel.sumar(it) },
        onRestar   = { viewModel.restar(it) },
        onEliminar = { viewModel.eliminar(it) }
    )

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentCarritoBinding.bind(view)

        binding.rvCarrito.layoutManager = LinearLayoutManager(requireContext())
        binding.rvCarrito.adapter = adapter

        // Items del carrito
        viewModel.items.observe(viewLifecycleOwner) { items ->
            adapter.submitList(items)
            val vacio = items.isEmpty()
            binding.layoutVacio.visibility   = if (vacio) View.VISIBLE else View.GONE
            binding.layoutResumen.visibility = if (vacio) View.GONE else View.VISIBLE
        }

        // Totales — equivalente a $subtotal, $impuestos, $total_final en carrito.php
        viewModel.subtotal.observe(viewLifecycleOwner) { sub ->
            binding.tvSubtotal.text = "$%.2f".format(sub ?: 0.0)
        }
        viewModel.impuestos.observe(viewLifecycleOwner) { imp ->
            binding.tvImpuestos.text = "$%.2f".format(imp)
        }
        viewModel.total.observe(viewLifecycleOwner) { total ->
            binding.tvTotal.text = "$%.2f".format(total)
        }

        // Vaciar carrito
        binding.btnVaciar.setOnClickListener { viewModel.vaciar() }

        // Proceder al pago (por ahora solo muestra mensaje)
        binding.btnProceder.setOnClickListener {
            findNavController().navigate(R.id.action_carritoFragment_to_checkoutFragment)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}