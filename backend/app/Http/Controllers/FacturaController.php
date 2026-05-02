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
            'idTarjeta'                  => 'required|exists:tarjeta,idTarjeta',
            'detalles'                   => 'required|array|min:1',
            'detalles.*.idProducto'      => 'required|exists:productos,idProducto',
            'detalles.*.cantidad'        => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = collect($request->detalles)->sum(fn($i) => $i['cantidad'] * $i['precio_unitario']);
            $itbms    = round($subtotal * 0.07, 2);
            $total    = $subtotal + $itbms;

            $tarjeta = Tarjeta::lockForUpdate()->find($request->idTarjeta);

            // ✅ str_contains es tolerante a tildes y mayúsculas
            $tipo = strtolower($tarjeta->tipo); // "débito" o "crédito"

            if (str_contains($tipo, 'debito') || str_contains($tipo, 'débito')) {
                if ($tarjeta->saldo < $total) {
                    DB::rollBack();
                    return response()->json([
                        'mensaje'          => 'Saldo insuficiente en la tarjeta de débito.',
                        'saldo_disponible' => $tarjeta->saldo,
                        'total_requerido'  => $total,
                    ], 422);
                }
                $tarjeta->saldo -= $total;
                $tarjeta->save();

            } elseif (str_contains($tipo, 'credito') || str_contains($tipo, 'crédito')) {
                $disponible = $tarjeta->saldoMaximo - $tarjeta->saldo;
                if ($disponible < $total) {
                    DB::rollBack();
                    return response()->json([
                        'mensaje'            => 'Límite de crédito insuficiente.',
                        'credito_disponible' => $disponible,
                        'total_requerido'    => $total,
                    ], 422);
                }
                $tarjeta->saldo += $total;
                $tarjeta->save();

            } else {
                // Tipo desconocido — loguear para debug
                DB::rollBack();
                return response()->json([
                    'mensaje' => 'Tipo de tarjeta no reconocido: ' . $tarjeta->tipo
                ], 422);
            }

            $factura = Factura::create([
                'idTarjeta' => $request->idTarjeta,
                'subtotal'  => $subtotal,
                'itbms'     => $itbms,
                'total'     => $total,
            ]);

            foreach ($request->detalles as $detalle) {
                FacturaDetalle::create([
                    'idFactura'       => $factura->idFactura,
                    'idProducto'      => $detalle['idProducto'],
                    'cantidad'        => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                ]);
            }

            DB::commit();
            return response()->json($factura->load('detalles.producto'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['mensaje' => 'Error interno', 'error' => $e->getMessage()], 500);
        }
    }
}