<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoUsuario;
class TipoUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {        
        return response()->json(TipoUsuario::where('estado', 'A')->get());
    }

    /**
     * Agregar nuevo rol
     */
    public function store(Request $request)
    {
        $request->validate([
          'nombre_tipo' => 'required|max:25'
        ]);
        $tipo = new TipoUsuario();
        $tipo ->nombre_tipo = $request->nombre_tipo;
        $tipo ->estado = 'A';
        $tipo->fecha_ingreso = now();
        $tipo->usuario_ingreso = 'Sistema';
        $tipo->fecha_modifica= now();
        $tipo->usuario_modifica= 'Sistema';
        $tipo->save();
        return response([
            'message' => 'Rol creado exitosamente',
            'data' => $tipo
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
          'nombre_tipo' => 'required|max:25'
        ]);
        $tipo = new TipoUsuario();
        $tipo ->nombre_tipo = $request->nombre_tipo;
        $tipo ->estado = 'A';
        $tipo->fecha_modifica= now();
        $tipo->usuario_modifica= 'Sistema';
        $tipo->update();
        return response([
            'message' => 'Rol actualizado exitosamente',
            'data' => $tipo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $tipo = TipoUsuario::findOrFail($id);
          $tipo->update(['estado' => 'I']);

        return response()->json([
            'message' => 'Rol eliminado'
        ]);
    }
}
