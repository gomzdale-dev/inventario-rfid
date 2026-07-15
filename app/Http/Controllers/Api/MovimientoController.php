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
                ->select('ubicaciones.id_ubicacion', 'laboratorios.id_laboratorio', 'laboratorios.nombre_laboratorio', 'edificios.id_edificio', 'edificios.nombre_edificio')
                ->where('ubicaciones.estado', 'A')
                ->get();

            $ubicacionesFormateadas = $ubicaciones->map(function($ubi) {
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

            $activos = DB::table('activos')
                ->leftJoin('etiquetas_rfid', 'activos.id_etiqueta', '=', 'etiquetas_rfid.id_etiqueta')
                ->select('activos.id_activo', 'activos.nombre_activo', 'activos.serie', 'etiquetas_rfid.codigo as codigo_rfid')
                ->get();

            return response()->json([
                'activos' => $activos,
                'tipos_movimiento' => $tiposMovimiento,
                'ubicaciones' => $ubicacionesFormateadas,
                'estados' => $estados
            ]);
        } catch (\Exception $e) {
            Log::error("Error en catalogos: " . $e->getMessage());
            return response()->json([
                'error' => 'Error interno en el servidor',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_activo'       => 'required|exists:activos,id_activo',
            'tipo_movimiento' => 'required|exists:tipo_movimientos,id',
            'id_laboratorio'  => 'required_without:id_ubicacion|exists:laboratorios,id_laboratorio',
            'id_ubicacion'    => 'required_without:id_laboratorio|exists:ubicaciones,id_ubicacion',
            'comentarios'     => 'nullable|string|max:50'
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

            Activo::query()
                ->where('id_activo', $validated['id_activo'])
                ->update($datosActualizar);

            $movimiento->load([
                'activo.etiqueta',
                'activo.responsable',
                'ubicacion.laboratorio.edificio',
                'tipoMovimiento',
                'usuario'
            ]);

            return response()->json(['message' => 'Movimiento registrado correctamente', 'movimiento' => $movimiento], 201);
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
