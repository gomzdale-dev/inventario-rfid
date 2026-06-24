<?php

namespace App\Http\Controllers\Api;

use App\Models\Inventario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InventarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Inventario::with(['detalles.activo.etiqueta', 'detalles.activo.ubicacion.laboratorio.edificio'])
                ->orderBy('fecha_inventario', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_inventario' => 'nullable|date',
            'id_usuario' => 'nullable|exists:usuarios,id_usuario'
        ]);

        $inventario = Inventario::create([
            'fecha_inventario' => $validated['fecha_inventario'] ?? now(),
            'id_usuario' => $validated['id_usuario'] ?? $request->user()?->id_usuario
        ]);

        return response()->json([
            'message' => 'Inventario registrado correctamente',
            'inventario' => $inventario
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Inventario::with(['detalles.activo.etiqueta', 'detalles.activo.ubicacion.laboratorio.edificio'])
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $validated = $request->validate([
            'fecha_inventario' => 'required|date',
            'id_usuario' => 'nullable|exists:usuarios,id_usuario'
        ]);

        $inventario->update($validated);

        return response()->json([
            'message' => 'Inventario actualizado correctamente',
            'inventario' => $inventario
        ]);
    }

    public function destroy($id)
    {
        $inventario = Inventario::findOrFail($id);
        $inventario->delete();

        return response()->json([
            'message' => 'Inventario eliminado correctamente'
        ]);
    }
}
