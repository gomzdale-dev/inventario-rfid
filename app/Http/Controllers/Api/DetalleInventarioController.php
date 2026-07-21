<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detalle_Inventario;
use Illuminate\Http\Request;

class DetalleInventarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Detalle_Inventario::with([
                'inventario.laboratorio.edificio',
                'activo.ubicacion.laboratorio.edificio',
                'activo.etiqueta',
                'activo.categoria',
                'activo.estado_activos'
            ])
                ->orderByDesc('id_detalle')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:500',
            'cantidad' => 'nullable|integer|min:1',
            'encontrado' => 'required|boolean',
            'fecha_lectura' => 'nullable|date',
            'id_inventario' => 'required|exists:inventarios,id_inventario',
            'id_activo' => 'required|exists:activos,id_activo'
        ]);

        $detalle = Detalle_Inventario::create([
            ...$validated,
            'cantidad' => $validated['cantidad'] ?? 1
        ]);

        return response()->json([
            'message' => 'Detalle registrado correctamente',
            'detalle' => $detalle->load([
                'inventario.laboratorio.edificio',
                'activo.etiqueta',
                'activo.ubicacion.laboratorio.edificio'
            ])
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Detalle_Inventario::with([
                'inventario.laboratorio.edificio',
                'activo.ubicacion.laboratorio.edificio',
                'activo.etiqueta'
            ])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $detalle = Detalle_Inventario::findOrFail($id);

        $validated = $request->validate([
            'observaciones' => 'nullable|string|max:500',
            'cantidad' => 'nullable|integer|min:1',
            'encontrado' => 'required|boolean',
            'fecha_lectura' => 'nullable|date',
            'id_inventario' => 'required|exists:inventarios,id_inventario',
            'id_activo' => 'required|exists:activos,id_activo'
        ]);

        $detalle->update([
            ...$validated,
            'cantidad' => $validated['cantidad'] ?? 1
        ]);

        return response()->json([
            'message' => 'Detalle actualizado correctamente',
            'detalle' => $detalle
        ]);
    }

    public function destroy($id)
    {
        $detalle = Detalle_Inventario::findOrFail($id);
        $detalle->delete();

        return response()->json([
            'message' => 'Detalle eliminado correctamente'
        ]);
    }
}
