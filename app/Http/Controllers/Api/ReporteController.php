<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Movimiento;
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
            return response()->json([
                'ubicaciones' => Ubicacion::query()
                    ->with(['laboratorio.edificio'])
                    ->where('estado', 'A')
                    ->get(),
                'estados' => Estado_Activo::query()
                    ->where('estado', 'A')
                    ->get(),
                'inventarios' => Inventario::query()
                    ->orderBy('fecha_inventario', 'desc')
                    ->get()
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
            'activos_categoria' => $this->reporteActivosCategoria($request),
            'activos_ubicacion' => $this->reporteActivosUbicacion($request),
            'activos_estado' => $this->reporteActivosEstado($request),
            'rfid_encontrados' => $this->reporteRfidEncontrados($request),
            'activos_no_encontrados' => $this->reporteActivosNoEncontrados($request),
            'historial_rfid', 'historial' => $this->reporteHistorialRfid($request),
            'diferencias_inventarios' => $this->reporteDiferenciasInventarios(),
            'mantenimiento' => $this->reporteMantenimiento($request),
            default => $this->reporteInventarioGeneral($request),
        };
    }

    private function baseActivosQuery(Request $request)
    {
        $query = Activo::query()->with([
            'etiqueta',
            'categoria',
            'modelo.marca',
            'ubicacion.laboratorio.edificio',
            'estado_activos',
            'responsable'
        ]);

        if ($request->filled('id_ubicacion')) {
            $query->where('id_ubicacion', $request->id_ubicacion);
        }

        if ($request->filled('id_estado')) {
            $query->where('id_estado', $request->id_estado);
        }

        return $query;
    }

    private function mapActivo(Activo $activo): array
    {
        return [
            'codigo' => $activo->id_activo,
            'activo' => $activo->nombre_activo,
            'serie' => $activo->serie,
            'categoria' => $activo->categoria->nombre_categoria ?? 'Sin categoría',
            'ubicacion' => $activo->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación',
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
        $data = $this->baseActivosQuery($request)
            ->orderBy('nombre_activo')
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

    private function reporteActivosCategoria(Request $request): array
    {
        $data = $this->baseActivosQuery($request)
            ->orderBy('id_categoria')
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                ['key' => 'categoria', 'label' => 'Categoría'],
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'rfid', 'label' => 'RFID'],
                ['key' => 'estado', 'label' => 'Estado'],
                ['key' => 'ubicacion', 'label' => 'Ubicación']
            ],
            'data' => $data
        ];
    }

    private function reporteActivosUbicacion(Request $request): array
    {
        $data = $this->baseActivosQuery($request)
            ->orderBy('id_ubicacion')
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                ['key' => 'edificio', 'label' => 'Edificio'],
                ['key' => 'ubicacion', 'label' => 'Laboratorio / Área'],
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

    private function reporteMantenimiento(Request $request): array
    {
        $data = $this->baseActivosQuery($request)
            ->whereHas('estado_activos', function ($q) {
                $q->where('nombre_estado', 'LIKE', '%Mantenimiento%');
            })
            ->orderBy('nombre_activo')
            ->get()
            ->map(fn (Activo $activo) => $this->mapActivo($activo))
            ->values();

        return [
            'columns' => [
                ['key' => 'codigo', 'label' => 'Código'],
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'rfid', 'label' => 'RFID'],
                ['key' => 'ubicacion', 'label' => 'Ubicación'],
                ['key' => 'responsable', 'label' => 'Responsable'],
                ['key' => 'estado', 'label' => 'Estado']
            ],
            'data' => $data
        ];
    }

    private function reporteRfidEncontrados(Request $request): array
    {
        $query = Movimiento::query()->with([
            'activo.etiqueta',
            'ubicacion.laboratorio.edificio'
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
            ->unique('id_activo')
            ->map(function (Movimiento $movimiento) {
                return [
                    'activo' => $movimiento->activo->nombre_activo ?? 'Sin activo',
                    'ubicacion_detectada' => $movimiento->ubicacion->laboratorio->nombre_laboratorio ?? 'Sin ubicación',
                    'fecha_hora_lectura' => $movimiento->fecha_movimiento,
                    'rfid' => $movimiento->activo->etiqueta->codigo ?? 'Sin RFID'
                ];
            })
            ->values();

        return [
            'columns' => [
                ['key' => 'activo', 'label' => 'Activo'],
                ['key' => 'ubicacion_detectada', 'label' => 'Ubicación detectada'],
                ['key' => 'fecha_hora_lectura', 'label' => 'Fecha y hora de lectura'],
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
