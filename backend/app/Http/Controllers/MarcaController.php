<?php
namespace App\Http\Controllers;
use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        return response()->json(Marca::all());
    }

    public function show($id)
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca no encontrada'], 404);
        }
        return response()->json($marca);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombreMarca' => 'required|string|max:25'
        ]);
        $marca = Marca::create($request->all());
        return response()->json($marca, 201);
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca no encontrada'], 404);
        }
        $marca->update($request->all());
        return response()->json($marca);
    }

    public function destroy($id)
    {
        $marca = Marca::find($id);
        if (!$marca) {
            return response()->json(['mensaje' => 'Marca no encontrada'], 404);
        }
        $marca->delete();
        return response()->json(['mensaje' => 'Marca eliminada']);
    }
}