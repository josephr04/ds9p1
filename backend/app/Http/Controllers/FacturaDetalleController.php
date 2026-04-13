<?php
namespace App\Http\Controllers;
use App\Models\FacturaDetalle;
use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaDetalleController extends Controller
{
    public function index($id)
    {
        $factura = Factura::find($id);
        if (!$factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        $detalle = FacturaDetalle::where('idFactura', $id)->get();
        return response()->json($detalle);
    }

    public function store(Request $request, $id)
    {
        $factura = Factura::find($id);
        if (!$factura) {
            return response()->json(['mensaje' => 'Factura no encontrada'], 404);
        }
        $request->validate([
            'idProducto'  => 'required|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'precioUnitario' => 'required|numeric|min:0',
        ]);
        $detalle = FacturaDetalle::create([
            ...$request->all(),
            'idFactura' => $id,
        ]);
        return response()->json($detalle, 201);
    }
}