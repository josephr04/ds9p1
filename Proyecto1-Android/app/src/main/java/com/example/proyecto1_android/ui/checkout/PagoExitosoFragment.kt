package com.example.proyecto1_android.ui.checkout

import android.os.Bundle
import android.view.View
import androidx.fragment.app.Fragment
import androidx.navigation.fragment.findNavController
import androidx.navigation.fragment.navArgs
import androidx.recyclerview.widget.LinearLayoutManager
import com.example.proyecto1_android.R
import com.example.proyecto1_android.databinding.FragmentPagoExitosoBinding

class PagoExitosoFragment : Fragment(R.layout.fragment_pago_exitoso) {

    private var _binding: FragmentPagoExitosoBinding? = null
    private val binding get() = _binding!!
    private val args: PagoExitosoFragmentArgs by navArgs()

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentPagoExitosoBinding.bind(view)

        binding.tvNumeroFactura.text    = "Factura #${args.idFactura}"
        binding.tvSubtotalExito.text    = "$%.2f".format(args.subtotal)
        binding.tvItbmsExito.text       = "$%.2f".format(args.itbms)
        binding.tvTotalExito.text       = "$%.2f".format(args.total)

        // Lista de productos de la factura
        val adapter = ResumenAdapter()
        binding.rvProductosFactura.layoutManager = LinearLayoutManager(requireContext())
        binding.rvProductosFactura.adapter = adapter
        adapter.submitList(args.items.toList())

        binding.btnVolverTienda.setOnClickListener {
            findNavController().popBackStack(R.id.tiendaFragment, false)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}