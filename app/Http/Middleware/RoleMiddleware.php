<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $usuario = $request->user();

        // Roles
        //1	Super Administrador
        //2 Administrador
        //3	Auditor
        //4	Contabilidad
        //5	Bodeguero
        $roles = explode(',', $role);

       if (!in_array($usuario->id_tipo, $roles)) {

         return response()->json(['message' => 'Acceso denegado'], 403);
        }
    }
}