<?php
namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    private function hashear(string $contrasena): string
    {
        return substr(md5($contrasena), 0, 25);
    }

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

        $empleado = Empleado::create([
            'usuario'    => $request->usuario,
            'nombre'     => $request->nombre,
            'apellido'   => $request->apellido,
            'rol'        => $request->rol,
            'contrasena' => $this->hashear($request->contrasena),
        ]);

        return response()->json($empleado, 201);
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::find($id);
        if (!$empleado) {
            return response()->json(['mensaje' => 'Empleado no encontrado'], 404);
        }

        $data = $request->only(['usuario', 'nombre', 'apellido', 'rol']);

        if ($request->filled('contrasena')) {
            $data['contrasena'] = $this->hashear($request->contrasena);
        }

        $empleado->update($data);
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

        if (!$empleado || $this->hashear($request->contrasena) !== $empleado->contrasena) {
            return response()->json(['mensaje' => 'Credenciales incorrectas'], 401);
        }

        return response()->json([
            'status'   => 'success',
            'usuario'  => $empleado->usuario,
            'nombre'   => $empleado->nombre,
            'apellido' => $empleado->apellido,
            'rol'      => $empleado->rol,
        ]);
    }
}