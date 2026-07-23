<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MovimientoController extends Controller
{
    public function index()
    {
        $movimientos = Movimiento::with([
            'activo.etiqueta',
            'activo.responsable',
            'ubicacion.laboratorio.edificio',
            'tipoMovimiento',
            'usuario'
        ])
        ->orderBy('fecha_movimiento', 'desc')
        ->get();

        return response()->json($movimientos);
    }

    public function catalogos()
    {
        try {
            $ubicaciones = DB::table('ubicaciones')
                ->join('laboratorios', 'ubicaciones.id_laboratorio', '=', 'laboratorios.id_laboratorio')
                ->join('edificios', 'laboratorios.id_edificio', '=', 'edificios.id_edificio')
                ->select(
                    'ubicaciones.id_ubicacion',
                    'laboratorios.id_laboratorio',
                    'laboratorios.nombre_laboratorio',
                    'edificios.id_edificio',
                    'edificios.nombre_edificio'
                )
                ->where('ubicaciones.estado', 'A')
                ->get();

            $ubicacionesFormateadas = $ubicaciones->map(function ($ubi) {
                return [
                    'id_ubicacion' => $ubi->id_ubicacion,
                    'id_laboratorio' => $ubi->id_laboratorio,
                    'id_edificio' => $ubi->id_edificio,
                    'laboratorio' => [
                        'id_laboratorio' => $ubi->id_laboratorio,
                        'nombre_laboratorio' => $ubi->nombre_laboratorio,
                        'edificio' => [
                            'id_edificio' => $ubi->id_edificio,
                            'nombre_edificio' => $ubi->nombre_edificio
                        ]
                    ]
                ];
            });

            $estados = DB::table('estado_activos')
                ->select('id_estado', 'nombre_estado')
                ->where('estado', 'A')
                ->get();

            $tiposMovimiento = DB::table('tipo_movimientos')
                ->select('id', 'nombre_movimiento')
                ->where('estado', 'A')
                ->get();

            return response()->json([
                'tipos_movimiento' => $tiposMovimiento,
                'ubicaciones' => $ubicacionesFormateadas,
                'estados' => $estados
            ]);
        } catch (\Exception $e) {
            Log::error('Error en catalogos de movimientos: ' . $e->getMessage());

            return response()->json([
                'message' => 'No fue posible cargar los catálogos de movimientos.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function activoPorRfid(string $codigo)
    {
        $codigo = trim(urldecode($codigo));

        $activo = DB::table('activos')
            ->join('etiquetas_rfid', 'activos.id_etiqueta', '=', 'etiquetas_rfid.id_etiqueta')
            ->leftJoin('ubicaciones', 'activos.id_ubicacion', '=', 'ubicaciones.id_ubicacion')
            ->leftJoin('laboratorios', 'ubicaciones.id_laboratorio', '=', 'laboratorios.id_laboratorio')
            ->leftJoin('edificios', 'laboratorios.id_edificio', '=', 'edificios.id_edificio')
            ->leftJoin('estado_activos', 'activos.id_estado', '=', 'estado_activos.id_estado')
            ->where('etiquetas_rfid.codigo', $codigo)
            ->select(
                'activos.id_activo',
                'activos.nombre_activo',
                'activos.serie',
                'activos.id_estado',
                'etiquetas_rfid.codigo as codigo_rfid',
                'etiquetas_rfid.estado as estado_etiqueta',
                'estado_activos.nombre_estado',
                'edificios.nombre_edificio',
                'laboratorios.nombre_laboratorio'
            )
            ->first();

        if (!$activo) {
            return response()->json([
                'message' => "La etiqueta RFID {$codigo} no está asociada a ningún activo registrado."
            ], 404);
        }

        $ubicacionActual = collect([
            $activo->nombre_edificio,
            $activo->nombre_laboratorio
        ])->filter()->implode(' - ');

        return response()->json([
            'message' => 'Activo identificado correctamente.',
            'activo' => [
                'id_activo' => $activo->id_activo,
                'nombre_activo' => $activo->nombre_activo,
                'serie' => $activo->serie,
                'codigo_rfid' => $activo->codigo_rfid,
                'id_estado' => $activo->id_estado,
                'nombre_estado' => $activo->nombre_estado,
                'estado_etiqueta' => $activo->estado_etiqueta,
                'ubicacion_actual' => $ubicacionActual ?: null
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_activo'       => 'required|exists:activos,id_activo',
            'tipo_movimiento' => 'required|exists:tipo_movimientos,id',
            'id_laboratorio'  => 'required_without:id_ubicacion|exists:laboratorios,id_laboratorio',
            'id_ubicacion'    => 'required_without:id_laboratorio|exists:ubicaciones,id_ubicacion',
            'comentarios'     => 'nullable|string|max:100'
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $ubicacionId = $validated['id_ubicacion'] ?? null;

            if (!$ubicacionId && isset($validated['id_laboratorio'])) {
                $ubicacion = Ubicacion::firstOrCreate(
                    ['id_laboratorio' => $validated['id_laboratorio']],
                    ['estado' => 'A']
                );

                $ubicacionId = $ubicacion->id_ubicacion;
            }

            $movimiento = Movimiento::create([
                'comentarios'      => $validated['comentarios'] ?? null,
                'tipo_movimiento'  => $validated['tipo_movimiento'],
                'fecha_movimiento' => now(),
                'id_usuario'       => $request->user()?->id_usuario,
                'id_activo'        => $validated['id_activo'],
                'id_ubicacion'     => $ubicacionId
            ]);

            $mapaEstados = [
                1 => 1,
                3 => 2,
                4 => 3,
            ];

            $datosActualizar = ['id_ubicacion' => $ubicacionId];

            if (isset($mapaEstados[$validated['tipo_movimiento']])) {
                $datosActualizar['id_estado'] = $mapaEstados[$validated['tipo_movimiento']];
            }

            $activo = Activo::findOrFail($validated['id_activo']);
            $activo->update($datosActualizar);

            $movimiento->load([
                'activo.etiqueta',
                'activo.responsable',
                'ubicacion.laboratorio.edificio',
                'tipoMovimiento',
                'usuario'
            ]);

            return response()->json([
                'message' => 'Movimiento registrado y ubicación del activo actualizada correctamente',
                'movimiento' => $movimiento
            ], 201);
        });
    }

    public function show($id)
    {
        $movimiento = Movimiento::with([
            'activo.etiqueta',
            'activo.responsable',
            'ubicacion.laboratorio.edificio',
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
