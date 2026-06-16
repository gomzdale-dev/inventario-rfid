<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movimiento;
use App\Models\Activo;
use App\Models\Tipo_Movimiento;
use App\Models\Ubicacion;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    public function index()
    {
        $movimientos = Movimiento::with([
            'activo.etiqueta',
            'activo.responsable',
            'ubicacion',
            'tipoMovimiento',
            'usuario'
        ])
        ->orderBy('fecha_movimiento', 'desc')
        ->get();

        return response()->json($movimientos);
    }

    public function catalogos()
    {
        return response()->json([
            'activos' => Activo::with(['etiqueta', 'responsable', 'ubicacion'])->get(),
            'tipos_movimiento' => Tipo_Movimiento::query()
            ->where('estado', '=', 'A')
            ->get(),

            'ubicaciones' => Ubicacion::query()
            ->where('estado', '=', 'A')
            ->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_activo' => 'required|exists:activos,id_activo',
            'tipo_movimiento' => 'required|exists:tipo_movimientos,id',
            'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio',
            'comentarios' => 'nullable|string|max:50'
        ]);

        $usuario = $request->user();

        $ubicacion = Ubicacion::firstOrCreate(
            [
                'id_laboratorio' => $validated['id_laboratorio']
            ],
            [
                'estado' => 'A'
            ]
        );

        $movimiento = Movimiento::create([
            'comentarios' => $validated['comentarios'] ?? null,
            'tipo_movimiento' => $validated['tipo_movimiento'],
            'fecha_movimiento' => now(),
            'id_usuario' => $usuario->id_usuario ?? null,
            'id_activo' => $validated['id_activo'],
            'id_ubicacion' => $ubicacion->id_ubicacion
        ]);

        $movimiento->load([
            'activo.etiqueta',
            'activo.responsable',
            'ubicacion',
            'tipoMovimiento',
            'usuario'
        ]);

        return response()->json([
            'message' => 'Movimiento registrado correctamente',
            'movimiento' => $movimiento
        ], 201);
    }

    public function show($id)
    {
        $movimiento = Movimiento::with([
            'activo.etiqueta',
            'activo.responsable',
            'ubicacion',
            'tipoMovimiento',
            'usuario'
        ])->findOrFail($id);

        return response()->json($movimiento);
    }

    public function destroy($id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $movimiento->delete();

        return response()->json([
            'message' => 'Movimiento eliminado correctamente'
        ]);
    }
}
