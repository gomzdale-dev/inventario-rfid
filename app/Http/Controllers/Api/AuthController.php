<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Usuario;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        
        $request->validate([
            'correo' => 'required|email',
            'password' => 'required',
        ]);

        // Buscar usuario
        $usuario = Usuario::where(
            'correo',
            $request->correo
        )->first();

        // Verificar si existe
        if (!$usuario) {

            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Verificar password
        if (!Hash::check(
            $request->password,
            $usuario->password
        )) {

            return response()->json([
                'message' => 'Contraseña incorrecta'
            ], 401);
        }
        $token = $usuario->createToken('api-token')->plainTextToken;
        // Login correcto
        return response()->json([
            'message' => 'Login correcto',
            'usuario' => $usuario,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // Eliminar token actual
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logout correcto'
        ]);
    }
}