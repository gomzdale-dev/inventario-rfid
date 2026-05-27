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
        //1	Administrador
        //2	Auditor
        //3	Contabilidad
        //4	Bodeguero
        $roles = explode(',', $role);

       if (!in_array($usuario->id_tipo, $roles)) {

         return response()->json(['message' => 'Acceso denegado'], 403);
        }
    }
}