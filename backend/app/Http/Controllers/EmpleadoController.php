<?php
namespace App\Http\Controllers;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'usuario'    => 'required|string|max:50|unique:empleado,usuario',
            'nombre'     => 'required|string|max:50',
            'apellido'   => 'required|string|max:50',
            'rol'        => 'required|in:1,2',
            'contrasena' => 'required|string|min:6',
        ]);

        $data = $request->all();
        // Hashear contraseña si quieres seguridad
        // $data['contrasena'] = Hash::make($request->contrasena);

        $empleado = Empleado::create($data);
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

    public function login(Request $request)
    {
        $request->validate([
            'usuario'    => 'required|string',
            'contrasena' => 'required|string',
        ]);

        $empleado = Empleado::where('usuario', $request->usuario)->first();

        if (!$empleado || $request->contrasena !== $empleado->contrasena) {
            return response()->json(['mensaje' => 'Credenciales incorrectas'], 401);
        }

        return response()->json([
            'status'  => 'success',
            'usuario' => $empleado->usuario,
            'nombre'  => $empleado->nombre,
            'rol'     => $empleado->rol,   // 1 = admin, 2 = empleado
        ]);
    }
}