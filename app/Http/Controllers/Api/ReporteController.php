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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class ReporteController extends Controller
{
    public function catalogos()
{
    try {
        // Obtenemos las ubicaciones de la base de datos de forma normal
        $ubicacionesOriginales = Ubicacion::query()
            ->with(['laboratorio.edificio'])
            ->where('estado', 'A')
            ->get();

        // Filtramos los duplicados usando la colección de Laravel para evitar el Error 500 de MySQL
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
            'id_estado' => 'nullable|integer'
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
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'El token de impresión provisto es inválido o ha expirado.'
                ], 401);
            }
        }

        $request->validate([
            'tipo_reporte' => 'required|string',
            'formato' => 'required|string|in:pdf,excel,json',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'id_ubicacion' => 'nullable|integer',
            'id_estado' => 'nullable|integer'
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

        return view('pdf.reporte_impresion', compact('data', 'tipoReporte'));
    }

    private function construirReporte(string $tipo, Request $request): array
    {
        return match ($tipo) {
            'inventario_general', 'inventario' => $this->reporteInventarioGeneral($request),
            'activos_ubicacion' => $this->reporteActivosUbicacion($request),
            'activos_estado' => $this->reporteActivosEstado($request),
            'activos_no_encontrados' => $this->reporteActivosNoEncontrados($request),
            'historial_rfid', 'historial' => $this->reporteHistorialRfid($request),
            'diferencias_inventarios' => $this->reporteDiferenciasInventarios(),
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
        'responsable'
    ]);

    if ($request->filled('id_ubicacion')) {
        // 1. Buscamos el registro de esa ubicación para saber su id_laboratorio
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

    private function mapActivo(Activo $activo): array
    {
       
          $laboratorio = $activo->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación';
          $edificio = $activo->ubicacion->laboratorio->edificio->nombre_edificio ?? null;

         
          $ubicacionCompleta = $edificio ? "{$laboratorio} ({$edificio})" : $laboratorio;
        return [
            'codigo' => $activo->id_activo,
            'activo' => $activo->nombre_activo,
            'serie' => $activo->serie,
            'categoria' => $activo->categoria->nombre_categoria ?? 'Sin categoría',
            'ubicacion' => $ubicacionCompleta,
            'edificio' => $activo->ubicacion->laboratorio->edificio->nombre_edificio ?? 'Sin edificio',
            'estado' => $activo->estado_activos->nombre_estado ?? 'Sin estado',
            'rfid' => $activo->etiqueta->codigo ?? 'Sin RFID',
            'responsable' => $activo->responsable
                ? $activo->responsable->nombre . ' ' . $activo->responsable->apellido
                : 'No asignado'
        ];
    }

    private function reporteInventarioGeneral(Request $request): array
    {
        $query = $this->baseActivosQuery($request);

        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $query->whereIn('activos.id_activo', function ($subquery) use ($request) {
                $subquery->select('di.id_activo')
                    ->from('detalle_inventarios as di')
                    ->join('inventarios as i', 'di.id_inventario', '=', 'i.id_inventario'); 

                if ($request->filled('fecha_inicio')) {
                    $subquery->whereDate('i.fecha_inventario', '>=', $request->fecha_inicio); 
                }

                if ($request->filled('fecha_fin')) {
                    $subquery->whereDate('i.fecha_inventario', '<=', $request->fecha_fin); 
                }
            });
        }

        $data = $query->orderBy('nombre_activo')
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'activo', 'label' => 'Nombre / Descripción'],
                ['key' => 'categoria', 'label' => 'Categoría'],
                ['key' => 'ubicacion', 'label' => 'Ubicación'],
                ['key' => 'estado', 'label' => 'Estado'],
                ['key' => 'rfid', 'label' => 'Etiqueta RFID']
            ],
            'data' => $data
        ];
    }
    private function reporteActivosUbicacion(Request $request): array
    {
        $query = $this->baseActivosQuery($request);

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_fin);
        }

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
               
                ['key' => 'ubicacion', 'label' => 'Ubicación (Edificio - Laboratorio)'],
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'rfid', 'label' => 'RFID'],
                ['key' => 'estado', 'label' => 'Estado']
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
                ['key' => 'estado', 'label' => 'Estado'],
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'categoria', 'label' => 'Categoría'],
                ['key' => 'ubicacion', 'label' => 'Ubicación'],
                ['key' => 'rfid', 'label' => 'RFID']
            ],
            'data' => $data
        ];
    }


    private function reporteActivosNoEncontrados(Request $request): array
    {
        $inventario = Inventario::query()->with('detalles')->orderBy('fecha_inventario', 'desc')->first();
        $activosDetectados = $inventario ? $inventario->detalles->pluck('id_activo')->toArray() : [];

        $data = $this->baseActivosQuery($request)
            ->whereNotIn('id_activo', $activosDetectados)
            ->orderBy('nombre_activo')
            ->get()
            ->map(function (Activo $activo) {
                return [
                    'codigo' => $activo->id_activo,
                    'descripcion' => $activo->nombre_activo,
                    'ultima_ubicacion' => $activo->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación',
                    'rfid' => $activo->etiqueta->codigo ?? 'Sin RFID'
                ];
            })
            ->values();

        return [
            'columns' => [
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'descripcion', 'label' => 'Descripción'],
                ['key' => 'ultima_ubicacion', 'label' => 'Última ubicación conocida'],
                ['key' => 'rfid', 'label' => 'RFID']
            ],
            'data' => $data
        ];
    }

    private function reporteHistorialRfid(Request $request): array
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

        if ($request->filled('id_ubicacion')) {
            $query->where('id_ubicacion', $request->id_ubicacion);
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
                    'usuario' => $movimiento->usuario->nombre_usuario ?? 'Sistema',
                    'ubicacion' => $movimiento->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación'
                ];
            })
            ->values();

        return [
            'columns' => [
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'rfid', 'label' => 'RFID'],
                ['key' => 'fecha', 'label' => 'Fecha'],
                ['key' => 'hora', 'label' => 'Hora'],
                ['key' => 'usuario', 'label' => 'Usuario'],
                ['key' => 'ubicacion', 'label' => 'Ubicación']
            ],
            'data' => $data
        ];
    }

    private function reporteDiferenciasInventarios(): array
    {
        $inventarios = Inventario::query()
            ->with('detalles.activo')
            ->orderBy('fecha_inventario', 'desc')
            ->take(2)
            ->get();

        if ($inventarios->count() < 2) {
            return [
                'columns' => [
                    ['key' => 'activo', 'label' => 'Activo'],
                    ['key' => 'inventario_anterior', 'label' => 'Inventario anterior'],
                    ['key' => 'inventario_reciente', 'label' => 'Inventario reciente'],
                    ['key' => 'resultado', 'label' => 'Resultado']
                ],
                'data' => []
            ];
        }

        $reciente = $inventarios[0];
        $anterior = $inventarios[1];

        $idsReciente = $reciente->detalles->pluck('id_activo')->toArray();
        $idsAnterior = $anterior->detalles->pluck('id_activo')->toArray();
        $todosIds = array_unique(array_merge($idsReciente, $idsAnterior));

        $activos = Activo::all()
    ->whereIn('id_activo', $todosIds)
    ->keyBy('id_activo');

        $data = collect($todosIds)->map(function ($id) use ($idsReciente, $idsAnterior, $activos, $reciente, $anterior) {
            $estabaAntes = in_array($id, $idsAnterior);
            $estaAhora = in_array($id, $idsReciente);

            if ($estabaAntes && $estaAhora) {
                $resultado = 'Se mantiene encontrado';
            } elseif (!$estabaAntes && $estaAhora) {
                $resultado = 'Nuevo encontrado';
            } else {
                $resultado = 'No encontrado en inventario reciente';
            }

            return [
                'activo' => $activos[$id]->nombre_activo ?? "Activo #$id",
                'inventario_anterior' => $anterior->fecha_inventario,
                'inventario_reciente' => $reciente->fecha_inventario,
                'resultado' => $resultado
            ];
        })->values();

        return [
            'columns' => [
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'inventario_anterior', 'label' => 'Inventario anterior'],
                ['key' => 'inventario_reciente', 'label' => 'Inventario reciente'],
                ['key' => 'resultado', 'label' => 'Resultado']
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
