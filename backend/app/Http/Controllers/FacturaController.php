<?php
namespace App\Http\Controllers;
use App\Models\Factura;
use App\Models\FacturaDetalle;
use App\Models\Tarjeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function index()
    {
        return response()->json(Factura::with(['tarjeta', 'detalles.producto'])->get());
    }

    public function show($id)
    {
        $factura = Factura::with(['tarjeta', 'detalles.producto'])->find($id);
        if (!$factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        return response()->json($factura);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idTarjeta'          => 'required|exists:tarjeta,idTarjeta',
            'detalles'           => 'required|array|min:1',
            'detalles.*.idProducto'      => 'required|exists:productos,idProducto',
            'detalles.*.cantidad'        => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric'
        ]);

        DB::beginTransaction();
        try {
            $subtotal = collect($request->detalles)->sum(function ($item) {
                return $item['cantidad'] * $item['precio_unitario'];
            });
            $itbms = $subtotal * 0.07;
            $total = $subtotal + $itbms;

            $factura = Factura::create([
                'idTarjeta' => $request->idTarjeta,
                'subtotal'  => $subtotal,
                'itbms'     => $itbms,
                'total'     => $total
            ]);

            foreach ($request->detalles as $detalle) {
                FacturaDetalle::create([
                    'idFactura'       => $factura->idFactura,
                    'idProducto'      => $detalle['idProducto'],
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario']
                ]);
            }

            DB::commit();
            return response()->json($factura->load('detalles.producto'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['mensaje' => 'Error al crear la factura', 'error' => $e->getMessage()], 500);
        }
    }
}