<?php

namespace App\Http\Controllers\Api;

use App\Models\Detalle_Inventario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DetalleInventarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Detalle_Inventario::with([
                'inventario',
                'activo.ubicacion.laboratorio.edificio',
                'activo.etiqueta',
                'activo.categoria',
                'activo.estado_activos'
            ])->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:50',
            'cantidad' => 'required|integer|min:1',
            'id_inventario' => 'required|exists:inventarios,id_inventario',
            'id_activo' => 'required|exists:activos,id_activo'
        ]);

        $detalle = Detalle_Inventario::create($validated);

        return response()->json([
            'message' => 'Detalle de inventario registrado correctamente',
            'detalle' => $detalle->load(['inventario', 'activo.etiqueta'])
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Detalle_Inventario::with([
                'inventario',
                'activo.ubicacion.laboratorio.edificio',
                'activo.etiqueta'
            ])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $detalle = Detalle_Inventario::findOrFail($id);

        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:50',
            'cantidad' => 'required|integer|min:1',
            'id_inventario' => 'required|exists:inventarios,id_inventario',
            'id_activo' => 'required|exists:activos,id_activo'
        ]);

        $detalle->update($validated);

        return response()->json([
            'message' => 'Detalle de inventario actualizado correctamente',
            'detalle' => $detalle
        ]);
    }

    public function destroy($id)
    {
        $detalle = Detalle_Inventario::findOrFail($id);
        $detalle->delete();

        return response()->json([
            'message' => 'Detalle de inventario eliminado correctamente'
        ]);
    }
}
