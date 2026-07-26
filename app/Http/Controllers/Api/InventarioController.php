<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Detalle_Inventario;
use App\Models\Edificio;
use App\Models\Inventario;
use App\Models\Laboratorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Inventario::with([
                'usuario',
                'laboratorio.edificio',
                'detalles.activo.etiqueta',
                'detalles.activo.ubicacion.laboratorio.edificio'
            ])
                ->orderByDesc('fecha_inventario')
                ->get()
        );
    }

    public function catalogos()
    {
        return response()->json([
            'edificios' => Edificio::query()
                ->orderBy('nombre_edificio')
                ->get(),

            'laboratorios' => Laboratorio::query()
                ->with('edificio')
                ->orderBy('nombre_laboratorio')
                ->get()
        ]);
    }

    public function activosPorUbicacion(Request $request)
    {
        $validated = $request->validate([
            'id_edificio' => 'required|exists:edificios,id_edificio',
            'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio'
        ]);

        $laboratorio = Laboratorio::query()
            ->with('edificio')
            ->where('id_laboratorio', $validated['id_laboratorio'])
            ->where('id_edificio', $validated['id_edificio'])
            ->firstOrFail();

        $activos = Activo::query()
            ->with([
                'etiqueta',
                'categoria',
                'modelo',
                'estado_activos',
                'responsable',
                'ubicacion.laboratorio.edificio'
            ])
            ->whereHas('ubicacion', function ($query) use ($validated) {
                $query->where('id_laboratorio', $validated['id_laboratorio']);
            })
            ->orderBy('nombre_activo')
            ->get()
            ->map(function (Activo $activo) {
                $edificio = $activo->ubicacion?->laboratorio?->edificio?->nombre_edificio;
                $laboratorio = $activo->ubicacion?->laboratorio?->nombre_laboratorio;

                $activo->ubicacion_formateada = collect([$edificio, $laboratorio])
                    ->filter()
                    ->implode(' - ');

                return $activo;
            });

        return response()->json([
            'ubicacion' => [
                'id_edificio' => $laboratorio->id_edificio,
                'nombre_edificio' => $laboratorio->edificio?->nombre_edificio,
                'id_laboratorio' => $laboratorio->id_laboratorio,
                'nombre_laboratorio' => $laboratorio->nombre_laboratorio
            ],
            'activos' => $activos
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_usuario'       => 'nullable|exists:usuarios,id_usuario',
            'id_laboratorio'   => 'required|exists:laboratorios,id_laboratorio', // <-- Agregado y requerido

            'detalles'                      => 'required|array|min:1',

            'detalles.*.id_activo'          => 'required|exists:activos,id_activo',
            'detalles.*.cantidad'           => 'nullable|integer|min:1',
            'detalles.*.observaciones'      => 'nullable|string|max:500',
            'detalles.*.encontrado'         => 'required|boolean',
            'detalles.*.fecha_lectura'      => 'nullable|date'
        ]);

        $inventario = DB::transaction(function () use ($validated, $request) {
            $inventario = Inventario::create([
                'fecha_inventario' => $validated['fecha_inventario'] ?? now(),
                'id_usuario'       => $validated['id_usuario'] ?? $request->user()?->id_usuario,
                'id_laboratorio'   => $validated['id_laboratorio'], // <-- Guardado correctamente
            ]);

            foreach ($validated['detalles'] as $detalle) {
                Detalle_Inventario::create([
                    'observaciones' => $detalle['observaciones'] ?? null,
                    'cantidad'      => $detalle['cantidad'] ?? 1,
                    'encontrado'    => $detalle['encontrado'],
                    'fecha_lectura' => $detalle['fecha_lectura'] ?? null,
                    'id_inventario' => $inventario->id_inventario,
                    'id_activo'     => $detalle['id_activo']
                ]);
            }

            return $inventario->load([
                'usuario',
                'laboratorio.edificio',
                'detalles.activo.etiqueta',
                'detalles.activo.ubicacion.laboratorio.edificio'
            ]);
        });

        return response()->json([
            'message' => 'Inventario RFID registrado correctamente',
            'resumen' => [
                'total'       => $inventario->detalles->count(),
                'encontrados' => $inventario->detalles->where('encontrado', true)->count(),
                'pendientes'  => $inventario->detalles->where('encontrado', false)->count()
            ],
            'inventario' => $inventario
        ], 201);
    }

    public function show($id)
    {
        return response()->json(
            Inventario::with([
                'usuario',
                'laboratorio.edificio',
                'detalles.activo.etiqueta',
                'detalles.activo.ubicacion.laboratorio.edificio'
            ])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $inventario = Inventario::findOrFail($id);

        $validated = $request->validate([
            'fecha_inventario' => 'required|date',
            'id_usuario' => 'nullable|exists:usuarios,id_usuario',
            'id_laboratorio' => 'nullable|exists:laboratorios,id_laboratorio'
        ]);

        $inventario->update($validated);

        return response()->json([
            'message' => 'Inventario actualizado correctamente',
            'inventario' => $inventario->load(['usuario', 'laboratorio.edificio'])
        ]);
    }

    public function destroy($id)
    {
        $inventario = Inventario::findOrFail($id);

        DB::transaction(function () use ($inventario) {
            $inventario->detalles()->delete();
            $inventario->delete();
        });

        return response()->json([
            'message' => 'Inventario eliminado correctamente'
        ]);
    }
}
