<?php
namespace App\Http\Controllers;
use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['categoria', 'marca']);

        if ($request->has('categoria_id')) {
            $query->where('idCategoria', $request->categoria_id);
        }
        if ($request->has('marca_id')) {
            $query->where('idMarca', $request->marca_id);
        }
        if ($request->has('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        $producto = Producto::with(['categoria', 'marca'])->find($id);
        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }
        return response()->json($producto);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:50',
            'unidad'      => 'required|string|max:20',
            'descripcion' => 'required|string|max:250',
            'stock'       => 'required|integer',
            'precioCosto' => 'required|numeric',
            'precioVenta' => 'required|numeric',
            'idCategoria' => 'required|exists:categoria,idCategoria',
            'idMarca'     => 'required|exists:marca,idMarca',
            'imagen'      => 'nullable|string'
        ]);
        $producto = Producto::create($request->all());
        return response()->json($producto, 201);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }
        $producto->update($request->all());
        return response()->json($producto);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        if (!$producto) {
            return response()->json(['mensaje' => 'Producto no encontrado'], 404);
        }
        $producto->delete();
        return response()->json(['mensaje' => 'Producto eliminado']);
    }
}