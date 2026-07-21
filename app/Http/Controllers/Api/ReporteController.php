<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Movimiento;
use App\Models\Edificio;
use App\Models\Laboratorio;
use App\Models\Estado_Activo;
use App\Models\Ubicacion;
use App\Models\Inventario;
use App\Models\Detalle_Inventario;
use App\Models\Tipo_Movimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class ReporteController extends Controller
{
    public function catalogos()
    {
        try {
            $ubicacionesOriginales = Ubicacion::query()
                ->with(['laboratorio.edificio'])
                ->where('estado', 'A')
                ->get();

            $ubicacionesUnicas = $ubicacionesOriginales->unique('id_laboratorio')->values();

            return response()->json([
                'ubicaciones' => $ubicacionesUnicas,
                'estados' => Estado_Activo::query()
                    ->where('estado', 'A')
                    ->get(),
                'inventarios' => Inventario::query()
                    ->orderBy('fecha_inventario', 'desc')
                    ->get(),
                'edificios' => Edificio::query()
                    ->orderBy('id_edificio', 'desc')
                    ->get(),   
                'laboratorios' => Laboratorio::query()
                    ->orderBy('id_laboratorio', 'desc')
                    ->get(),  
                'movimientos' => Tipo_Movimiento::select('id', 'nombre_movimiento as nombre')->get(), 
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function resumenReportes()
    {
        return response()->json([
            'total_activos' => DB::table('activos')->count(),
            'total_movimientos' => DB::table('movimientos')->count(),
            'total_inventarios' => DB::table('inventarios')->count(),
            'total_mantenimiento' => Activo::query()
                ->whereHas('estado_activos', function ($q) {
                    $q->where('nombre_estado', 'LIKE', '%Mantenimiento%');
                })
                ->count()
        ]);
    }

    public function vistaReporte(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|string',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'id_ubicacion' => 'nullable|integer',
            'id_estado' => 'nullable|integer',
            'tipo_movimiento' => 'nullable|string'
        ]);

        return response()->json($this->construirReporte($request->tipo_reporte, $request));
    }

    public function exportarReporte(Request $request)
    {
        $request->headers->set('Accept', 'application/json');

        if ($request->isMethod('get') && $request->has('token')) {
            $tokenRaw = urldecode((string) $request->query('token'));
            $model = PersonalAccessToken::findToken($tokenRaw);

            if ($model && $model->tokenable) {
                app('auth')->guard('web')->login($model->tokenable);
            } else {
                return response()->json(['error' => 'No autorizado'], 401);
            }
        }

        $request->validate([
            'tipo_reporte' => 'required|string',
            'formato' => 'required|string|in:pdf,excel,json',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'id_ubicacion' => 'nullable|integer',
            'id_estado' => 'nullable|integer',
            'tipo_movimiento' => 'nullable|string'
        ]);

        $resultado = $this->construirReporte($request->tipo_reporte, $request);
        $tipoReporte = $request->tipo_reporte;
        $data = collect($resultado['data']);

        if ($request->formato === 'json') {
            return response()->json([
                'success' => true,
                'tipo' => $tipoReporte,
                'total_registros' => $data->count(),
                'columns' => $resultado['columns'],
                'data' => $data
            ]);
        }

        if ($request->formato === 'excel') {
            return $this->exportarCsvNativo($tipoReporte, $resultado['columns'], $data);
        }

        return view('pdf.reporte_impresion', compact('resultado', 'data', 'tipoReporte'));
    }

    private function construirReporte(string $tipo, Request $request): array
    {
        return match ($tipo) {
            'inventario_general', 'inventario' => $this->reporteInventarioGeneral($request),
            'activos_por_ubicacion' => $this->reporteActivosUbicacion($request),
            'activos_por_estado' => $this->reporteActivosEstado($request),
            'historial_por_movimiento', 'historial' => $this->reporteHistorialMovimiento($request),
            default => $this->reporteInventarioGeneral($request),
        };
    }

    private function baseActivosQuery(Request $request)
    {
        $query = Activo::query()->select('activos.*')->with([
            'etiqueta',
            'categoria',
            'modelo.marca',
            'ubicacion.laboratorio.edificio',
            'estado_activos',
            'responsable',
            'detalleInventarios.inventario'
        ]);

        if ($request->filled('id_ubicacion')) {
            $ubicacionBase = Ubicacion::find($request->id_ubicacion);

            if ($ubicacionBase) {
                $query->whereHas('ubicacion', function ($q) use ($ubicacionBase) {
                    $q->where('id_laboratorio', $ubicacionBase->id_laboratorio);
                });
            } else {
                $query->where('id_ubicacion', 0);
            }
        }

        if ($request->filled('id_estado')) {
            $query->where('activos.id_estado', $request->id_estado);
        }

        return $query;
    }

   private function mapActivo(Activo $activo, ?Request $request = null): array
    {
        $edificio = 'Sin edificio';
        if ($activo->ubicacion && $activo->ubicacion->laboratorio && $activo->ubicacion->laboratorio->edificio) {
            $lab = $activo->ubicacion->laboratorio->nombre_laboratorio ?? '';
            $ed = $activo->ubicacion->laboratorio->edificio->nombre_edificio ?? '';
            $edificio = $ed ? "$ed - $lab" : $lab;
        }

        // Si hay un rango de fechas en la petición, filtramos el detalle que pertenezca a ese rango
        $detalles = $activo->detalleInventarios;

        if ($request && ($request->filled('fecha_inicio') || $request->filled('fecha_fin'))) {
            $fInicio = $request->filled('fecha_inicio') ? date('Y-m-d', strtotime(str_replace('/', '-', $request->input('fecha_inicio')))) : null;
            $fFin = $request->filled('fecha_fin') ? date('Y-m-d', strtotime(str_replace('/', '-', $request->input('fecha_fin')))) : null;

            $detalles = $detalles->filter(function($detalle) use ($fInicio, $fFin) {
                $fechaInv = optional($detalle->inventario)->fecha_inventario;
                if (!$fechaInv) return false;
                
                $valido = true;
                if ($fInicio) $valido = $valido && ($fechaInv >= $fInicio);
                if ($fFin) $valido = $valido && ($fechaInv <= $fFin);
                return $valido;
            });
        }

        $ultimoDetalle = $detalles->sortByDesc(function ($detalle) {
            return optional($detalle->inventario)->fecha_inventario;
        })->first();

       

        $fechaInventario = optional(optional($ultimoDetalle)->inventario)->fecha_inventario;
        $fechaFormateada = $fechaInventario ? date('d-m-Y', strtotime($fechaInventario)) : 'N/A';

        $valorActual = $activo->valor_actual ?? 0.00;
        $depreciacion = $activo->depreciacion_anual ?? 0.00;
        $vidaUtil = $activo->vida_util ?? 0;

        return [
            'Fecha_Inventario'   => $fechaFormateada,
            'codigo'             => $activo->id_activo,
            'activo'             => $activo->nombre_activo ?? 'Sin nombre',
            'valor_actual'       => '$ ' . number_format($valorActual, 2, '.', ','),
            'depreciacion_anual' => '$ ' . number_format($depreciacion, 2, '.', ','),
            'vida_util'          => $vidaUtil . ' Años',
            'categoria'          => optional($activo->categoria)->nombre_categoria ?? 'Sin categoría',
            'edificio'           => $edificio,
            'ubicacion'          => $edificio,
            'estado'             => optional($activo->estado_activos)->nombre_estado ?? 'Normal',
            'rfid'               => optional($activo->etiqueta)->codigo ?? 'Sin RFID',
            'observaciones'      => optional($ultimoDetalle)->observaciones ?? 'Sin observaciones'
        ];
    }

    private function reporteActivosUbicacion(Request $request): array
    {
        $query = $this->baseActivosQuery($request);

        $data = $query->join('ubicaciones', 'activos.id_ubicacion', '=', 'ubicaciones.id_ubicacion')
            ->join('laboratorios', 'ubicaciones.id_laboratorio', '=', 'laboratorios.id_laboratorio')
            ->join('edificios', 'laboratorios.id_edificio', '=', 'edificios.id_edificio')
            ->orderBy('edificios.nombre_edificio', 'asc')
            ->orderBy('laboratorios.nombre_laboratorio', 'asc')
            ->orderBy('activos.nombre_activo', 'asc')
            ->select('activos.*') 
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                 
                 ['key' => 'activo', 'label' => 'Activo'],
                 ['key' => 'valor_actual', 'label' => 'Valor Actual'],
                 ['key' => 'depreciacion_anual', 'label' => 'Depreciación Anual'],
                 ['key' => 'vida_util', 'label' => 'Vida Útil'],
                 ['key' => 'categoria', 'label' => 'Categoría'],
            ],
            'data' => $data
        ];
    }

    private function reporteActivosEstado(Request $request): array
    {
        $data = $this->baseActivosQuery($request)
            ->orderBy('id_estado')
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                ['key' => 'activo', 'label' => 'Activo'],
                 ['key' => 'valor_actual', 'label' => 'Valor Actual'],
                 ['key' => 'depreciacion_anual', 'label' => 'Depreciación Anual'],
                 ['key' => 'vida_util', 'label' => 'Vida Útil'],
                 ['key' => 'categoria', 'label' => 'Categoría'],
                ['key' => 'ubicacion', 'label' => 'Ubicación']
                
            ],
            'data' => $data
        ];
    }

    private function reporteInventarioGeneral(Request $request): array
   {
    $query = Inventario::with([
        'detalles.activo.categoria',
        'detalles.activo.ubicacion.laboratorio.edificio',
        'detalles.activo.estado_activos',
        'detalles.activo.etiqueta',
        'detalles.activo.responsable'
    ]);

    $query->whereDate('fecha_inventario', '>=', $request->fecha_inicio)
      ->whereDate('fecha_inventario', '<=', $request->fecha_fin);

    $inventarios = $query
        ->orderBy('fecha_inventario', 'asc')
        ->get();

    $data = collect();

    foreach ($inventarios as $inventario) {

        foreach ($inventario->detalles as $detalle) {

            $activo = $detalle->activo;

            if (!$activo) {
                continue;
            }

            $edificio = 'Sin edificio';

            if (
                $activo->ubicacion &&
                $activo->ubicacion->laboratorio &&
                $activo->ubicacion->laboratorio->edificio
            ) {
                $edificio =
                    $activo->ubicacion->laboratorio->edificio->nombre_edificio .
                    ' - ' .
                    $activo->ubicacion->laboratorio->nombre_laboratorio;
            }

            $data->push([
                'Fecha_Inventario'   => date('d-m-Y', strtotime($inventario->fecha_inventario)),
                'activo'             => $activo->nombre_activo,
                'valor_actual'       => '$ ' . number_format($activo->valor_actual, 2),
                'depreciacion_anual' => '$ ' . number_format($activo->depreciacion_anual, 2),
                'vida_util'          => $activo->vida_util . ' Años',
                'categoria'          => optional($activo->categoria)->nombre_categoria ?? 'Sin categoría',
                'edificio'           => $edificio,
                'observaciones'      => $detalle->observaciones ?? 'Sin observaciones'
            ]);
        }
    }

    return [
        'columns' => [
            ['key' => 'Fecha_Inventario', 'label' => 'Fecha Inventario'],
            ['key' => 'activo', 'label' => 'Activo'],
            ['key' => 'valor_actual', 'label' => 'Valor Actual'],
            ['key' => 'depreciacion_anual', 'label' => 'Depreciación Anual'],
            ['key' => 'vida_util', 'label' => 'Vida Útil'],
            ['key' => 'categoria', 'label' => 'Categoría'],
            ['key' => 'edificio', 'label' => 'Edificio'],
            ['key' => 'observaciones', 'label' => 'Observaciones']
        ],
        'data' => $data
    ];
    }
    
    private function reporteHistorialMovimiento(Request $request): array
    {
        $query = Movimiento::query()->with([
            'activo.etiqueta',
            'ubicacion.laboratorio.edificio',
            'usuario'
        ]);

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_movimiento', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_movimiento', '<=', $request->fecha_fin);
        }

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }

        $data = $query->orderBy('fecha_movimiento', 'desc')
            ->get()
            ->map(function (Movimiento $movimiento) {
                $fecha = $movimiento->fecha_movimiento ? date('Y-m-d', strtotime($movimiento->fecha_movimiento)) : 'N/A';
                $hora = $movimiento->fecha_movimiento ? date('H:i:s', strtotime($movimiento->fecha_movimiento)) : 'N/A';

                return [
                    'activo' => $movimiento->activo->nombre_activo ?? 'Sin activo',
                    'rfid' => $movimiento->activo->etiqueta->codigo ?? 'Sin RFID',
                    'fecha' => $fecha,
                    'hora' => $hora,
                    'tipo_movimiento' => $movimiento->tipo_movimiento ?? 'N/A',
                    'usuario' => $movimiento->usuario->nombre_usuario ?? 'Sistema',
                    'ubicacion' => $movimiento->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación',
                    'categoria' => $movimiento->activo->categoria->nombre_categoria,
                    'comentarios' => $movimiento->comentarios,
                ];
            })
            ->values();

        return [
            'columns' => [
                ['key' => 'rfid', 'label' => 'RFID'],
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'categoria', 'label' => 'Categoría'],
                ['key' => 'ubicacion', 'label' => 'Ubicación'],
                ['key' => 'comentarios', 'label' => 'Comentarios'], 
                ['key' => 'usuario', 'label' => 'Usuario'],                             
                ['key' => 'fecha', 'label' => 'Fecha'],
                ['key' => 'hora', 'label' => 'Hora'],
            ],
            'data' => $data
        ];
    }

   

    private function exportarCsvNativo($tipo, $columns, $data)
    {
        $fileName = "reporte_" . $tipo . "_" . date('Ymd_His') . ".csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Expires" => "0",
            "Pragma" => "public"
        ];

        $callback = function () use ($columns, $data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $delimitador = ';';

            fputcsv($file, collect($columns)->pluck('label')->toArray(), $delimitador);

            foreach ($data as $row) {
                $line = [];

                foreach ($columns as $column) {
                    $line[] = $row[$column['key']] ?? 'N/A';
                }

                fputcsv($file, $line, $delimitador);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}