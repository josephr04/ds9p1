<?php
namespace App\Http\Controllers;
use App\Models\Tarjeta;
use Illuminate\Http\Request;

class TarjetaController extends Controller
{
    public function index()
    {
        return response()->json(Tarjeta::all());
    }

    public function show($id)
    {
        $tarjeta = Tarjeta::find($id);
        if (!$tarjeta) {
            return response()->json(['mensaje' => 'Tarjeta no encontrada'], 404);
        }
        return response()->json($tarjeta);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipoTarjeta' => 'required|string|max:20',
        ]);
        $tarjeta = Tarjeta::create($request->all());
        return response()->json($tarjeta, 201);
    }

    public function update(Request $request, $id)
    {
        $tarjeta = Tarjeta::find($id);
        if (!$tarjeta) {
            return response()->json(['mensaje' => 'Tarjeta no encontrada'], 404);
        }
        $tarjeta->update($request->all());
        return response()->json($tarjeta);
    }

    public function destroy($id)
    {
        $tarjeta = Tarjeta::find($id);
        if (!$tarjeta) {
            return response()->json(['mensaje' => 'Tarjeta no encontrada'], 404);
        }
        $tarjeta->delete();
        return response()->json(['mensaje' => 'Tarjeta eliminada']);
    }
}