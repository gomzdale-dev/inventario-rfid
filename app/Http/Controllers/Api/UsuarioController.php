<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\TipoUsuario;


class UsuarioController extends Controller
{
    
    //Mostrar registros
    public function index(Request $request)
    {
        return response()->json(Usuario::with('tipoUsuario')->get());
        
    }


    //Crear
    public function store(Request $request)
    {
       $request->validate([
          'nombre_usuario' => 'required',
          'correo' => 'required|email',
          'password' => 'required|min:6',
          'estado' => 'required|in:A,I',
          'id_tipo' => 'required|exists:tipo_usuarios,id'
        ]);

        $usuario = new Usuario();
        $usuario->nombre_usuario = $request->nombre_usuario;
        $usuario->correo = $request->correo;
        $usuario->password = bcrypt($request->password);
        $usuario->estado = $request->estado;
        $usuario->fecha_ingreso = now();
        $usuario->usuario_ingreso = 'Sistema';
        $usuario->fecha_modifica= now();
        $usuario->usuario_modifica= 'Sistema';
        $usuario->id_tipo = $request->id_tipo;
        
        $usuario->save();
        return response([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    // Actualziar registro
   public function update(Request $request, string $id)
{
    $usuario = Usuario::findOrFail($id);

    //  Validación SOLO si vienen los campos
    $request->validate([
        'nombre_usuario' => 'sometimes|required',
        'correo' => 'sometimes|required|email',
        'estado' => 'sometimes|required|in:A,I',
        'id_tipo' => 'sometimes|required|exists:tipo_usuarios,id',
        'password' => 'sometimes|required'
    ]);

    //  Actualizar solo lo que venga
    if ($request->has('nombre_usuario')) {
        $usuario->nombre_usuario = $request->nombre_usuario;
    }

    if ($request->has('correo')) {
        $usuario->correo = $request->correo;
    }

    if ($request->has('estado')) {
        $usuario->estado = $request->estado;
    }

    if ($request->has('id_tipo')) {
        $usuario->id_tipo = $request->id_tipo;
    }

    if ($request->has('password')) {
        $usuario->password = Hash::make($request->password);
    }

    $usuario->fecha_modifica = now();

    $usuario->update();

    return response()->json([
        'message' => 'Usuario actualizado',
        'usuario' => $usuario
    ]);
}
    //Eliminar usuario
    public function destroy($id)
    {
          $usuario = Usuario::findOrFail($id);
          $usuario->update(['estado' => 'I']);

        return response()->json([
            'message' => 'Usuario eliminado'
        ]);
    }

    
}
