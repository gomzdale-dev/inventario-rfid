<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Estado_Activo;
use App\Models\Movimiento;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
    try {
        // 1. Ubicaciones (Tu consulta original con DB::table está bien)
        $ubicaciones = DB::table('ubicaciones')
            ->join('laboratorios', 'ubicaciones.id_laboratorio', '=', 'laboratorios.id_laboratorio')
            ->join('edificios', 'laboratorios.id_edificio', '=', 'edificios.id_edificio')
            ->select('ubicaciones.id_ubicacion', 'laboratorios.nombre_laboratorio', 'edificios.nombre_edificio')
            ->where('ubicaciones.estado', 'A')
            ->get();

        $ubicacionesFormateadas = $ubicaciones->map(function($ubi) {
            return [
                'id_ubicacion' => $ubi->id_ubicacion,
                'laboratorio' => [
                    'nombre_laboratorio' => $ubi->nombre_laboratorio,
                    'edificio' => ['nombre_edificio' => $ubi->nombre_edificio]
                ]
            ];
        });

        // 2. Estados: Cambiado de Estado_Activo::table() a Estado_Activo::all() o DB::table()
        $estados = DB::table('estado_activos')
            ->select('id_estado', 'nombre_estado')
            ->where('estado', 'A')
            ->get();

        // 3. Activos: Cambiado de Activo::table() a DB::table()
        $activos = DB::table('activos')
            ->leftJoin('etiquetas_rfid', 'activos.id_etiqueta', '=', 'etiquetas_rfid.id_etiqueta')
            ->select('activos.id_activo', 'activos.nombre_activo', 'etiquetas_rfid.codigo as codigo_rfid')
            ->get();

        return response()->json([
            'activos'     => $activos,
            'ubicaciones' => $ubicacionesFormateadas,
            'estados'     => $estados
        ]);

   
    } catch (\Exception $e) {
        Log::error("Error en catalogos: " . $e->getMessage());
        return response()->json([
            'error'   => 'Error interno en el servidor',
            'details' => $e->getMessage()
        ], 500);
    }
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