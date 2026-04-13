<?php
namespace App\Http\Controllers;
use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    public function index()
    {
        return response()->json(Empleado::all());
    }

    public function show($id)
    {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            return response()->json(['mensaje' => 'Empleado no encontrado'], 404);
        }
        return response()->json($empleado);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombreEmp'   => 'required|string|max:50',
            'apellidoEmp' => 'required|string|max:50',
            'correoEmp'   => 'required|email|unique:empleado,correoEmp',
            'telefonoEmp' => 'nullable|string|max:15',
        ]);
        $empleado = Empleado::create($request->all());
        return response()->json($empleado, 201);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            return response()->json(['mensaje' => 'Empleado no encontrado'], 404);
        }
        $empleado->update($request->all());
        return response()->json($empleado);
    }

    public function destroy($id)
    {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            return response()->json(['mensaje' => 'Empleado no encontrado'], 404);
        }
        $empleado->delete();
        return response()->json(['mensaje' => 'Empleado eliminado']);
    }
}