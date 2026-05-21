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
        return response()->json(Usuario::with('tipoUsuario')
        ->where('estado', 'A')->get());
        
    }


    //Crear
    public function store(Request $request)
    {

       $usuarioAuth = $request->user();
       $request->validate([
          'nombre_usuario' => 'required',
          'correo' => 'required|email',
          'password' => 'required|min:8',
          'id_tipo' => 'required|exists:tipo_usuarios,id'
        ]);

        $usuario = new Usuario();
        $usuario->nombre_usuario = $request->nombre_usuario;
        $usuario->correo = $request->correo;
        $usuario->password = bcrypt($request->password);
        $usuario->estado = 'A';
        $usuario->fecha_ingreso = now();
        $usuario->usuario_ingreso = $usuarioAuth->nombre_usuario;;
        $usuario->fecha_modifica= now();
        $usuario->usuario_modifica=  $usuarioAuth->nombre_usuario;;
        $usuario->id_tipo = $request->id_tipo;
        
        $usuario->save();
        return response([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ]);
    }

   

    // Actualziar registro
   public function update(Request $request, string $id)
{
    $usuarioAuth = $request->user();
    $usuario = Usuario::findOrFail($id);

    //  Validación SOLO si vienen los campos
    $request->validate([
        'nombre_usuario' => 'sometimes|required',
        'correo' => 'sometimes|required|email',
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

    if ($request->has('id_tipo')) {
        $usuario->id_tipo = $request->id_tipo;
    }

    if ($request->has('password')) {
        $usuario->password = Hash::make($request->password);
    }
    $usuario->usuario_modifica-> $usuarioAuth->nombre_usuario;
    $usuario->fecha_modifica = now();

    $usuario->update();

    return response()->json([
        'message' => 'Usuario actualizado',
        'usuario' => $usuario
    ]);
   }
    //Eliminar usuario
    public function destroy(Request $request, string $id)
    {
       $usuarioAuth = $request->user();
       $usuario = Usuario::findOrFail($id);

       $usuario->estado = 'I';
       $usuario->fecha_modifica = now();
       $usuario->usuario_modifica= $usuarioAuth->nombre_usuario;

       $usuario->save();

    return response()->json([
        'message' => 'Usuario eliminado correctamente'
    ]);
   }

    
}
