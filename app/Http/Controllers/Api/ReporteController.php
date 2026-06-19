<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;

use App\Models\Tipo_Movimiento;
use App\Models\Movimiento;
use App\Models\Estado_Activo;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteController extends Controller
{
    
    public function exportarReporte(Request $request)
    {
        // FORZAR RESPUESTA JSON: Evita el Route [login] not defined
        $request->headers->set('Accept', 'application/json');

        // Si viene un token en la URL (petición GET del PDF), autenticamos manualmente
        if ($request->isMethod('get') && $request->has('token')) {
            $tokenRaw = $request->query('token');
            
            if (str_contains($tokenRaw, '%7C')) {
                $tokenRaw = urldecode($tokenRaw);
            }

            $model = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenRaw);
            
            if ($model && $model->tokenable) {
                auth()->login($model->tokenable);
            } else {
                return response()->json([
                    'error' => 'No autorizado', 
                    'message' => 'El token de impresión provisto es inválido o ha expirado.'
                ], 401);
            }
        }

        // Validación de datos limpia para peticiones tanto GET como POST
        $request->validate([
            'tipo_reporte' => 'required|string|in:historial,inventario,mantenimiento',
            'formato'      => 'required|string|in:pdf,excel,json',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin'    => 'nullable|date',
            'id_ubicacion' => 'nullable|integer',
            'id_estado'    => 'nullable|integer',
        ]);

        $tipoReporte = $request->tipo_reporte;
        $data = collect();

        // REPORTE 1: HISTORIAL DE MOVIMIENTOS
        if ($tipoReporte === 'historial') {
            $query = Movimiento::with([
                'activo.etiqueta',
                'activo.modelo.marca',
                'activo.categoria',
                'ubicacion.laboratorio.edificio',
                'tipoMovimiento',
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
            if ($request->filled('id_estado')) {
                $query->whereHas('activo', function ($q) use ($request) {
                    $q->where('id_estado', $request->id_estado);
                });
            }

            $data = $query->orderBy('fecha_movimiento', 'desc')->get();
        }

        // REPORTE 2: INVENTARIO ACTUALIZADO
       if ($tipoReporte === 'inventario') {
    $query = Activo::with([
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
    if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
        $query->whereHas('detalleInventarios.inventario', function ($q) use ($request) {
            $q->whereBetween('fecha_inventario', [$request->fecha_inicio, $request->fecha_fin]);
        });
    }

    $data = $query->orderBy('nombre_activo', 'asc')->get();
}

        // REPORTE 3: EQUIPOS EN MANTENIMIENTO
        if ($tipoReporte === 'mantenimiento') {
            $query = Activo::with([
                'etiqueta',
                'modelo.marca',
                'ubicacion.laboratorio.edificio',
                'estado_activos',
                'responsable'
            ])->whereHas('estado_activos', function ($q) {
                $q->where('nombre_estado', 'LIKE', '%Mantenimiento%');
            });

            if ($request->filled('id_ubicacion')) {
                $query->where('id_ubicacion', $request->id_ubicacion);
            }
            
            if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
                $query->whereHas('movimientos', function ($q) use ($request) {
                    $q->whereDate('fecha_movimiento', '>=', $request->fecha_inicio)
                      ->whereDate('fecha_movimiento', '<=', $request->fecha_fin);
                });
            }

            $data = $query->orderBy('nombre_activo', 'asc')->get();
        }
        if ($request->formato === 'json') {
            return response()->json([
                'success' => true,
                'tipo' => $tipoReporte,
                'total_registros' => $data->count(),
                'data' => $data
            ]);
        }

        if ($request->formato === 'excel') {
            return $this->exportarCsvNativo($tipoReporte, $data);
        }

        if ($request->formato === 'pdf') {
            return view('pdf.reporte_impresion', compact('data', 'tipoReporte'));
        }
    }

    private function exportarCsvNativo($tipo, $data)
    {
        $fileName = "reporte_" . $tipo . "_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Expires"             => "0",
            "Pragma"              => "public"
        ];

        $callback = function() use($tipo, $data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 
            $delimitador = ';';

            if ($tipo === 'historial') {
                fputcsv($file, ['Fecha Movimiento', 'Tipo Movimiento', 'Código RFID', 'Activo', 'Ubicación / Laboratorio', 'Responsable', 'Comentarios'], $delimitador);
                
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->fecha_movimiento ?? 'N/A',
                        $row->tipoMovimiento->nombre_movimiento ?? 'N/A',
                        $row->activo->etiqueta->codigo ?? 'Sin Tag',
                        $row->activo->nombre_activo ?? 'N/A',
                        $row->ubicacion->laboratorio->nombre_laboratorio ?? 'N/A',
                        (($row->activo && $row->activo->responsable) ? ($row->activo->responsable->nombre . ' ' . $row->activo->responsable->apellido) : 'No Asignado'),
                        $row->comentarios ?? ''
                    ], $delimitador);
                }
            } else {
                fputcsv($file, ['Código RFID', 'Nombre del Activo', 'Serie', 'Categoría', 'Modelo', 'Marca', 'Ubicación / Laboratorio', 'Estado', 'Responsable', 'Valor Compra', 'Fecha Compra'], $delimitador);
                
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->etiqueta->codigo ?? 'Sin Tag',
                        $row->nombre_activo ?? 'N/A',
                        $row->serie ?? 'N/A',
                        $row->categoria->nombre_categoria ?? 'N/A',
                        $row->modelo->nombre_modelo ?? 'N/A',
                        $row->modelo->marca->nombre_marca ?? 'N/A',
                        $row->ubicacion->laboratorio->nombre_laboratorio ?? 'N/A',
                        $row->estado_activo->nombre_estado ?? 'N/A',
                        ($row->responsable ? ($row->responsable->nombre . ' ' . $row->responsable->apellido) : 'No Asignado'),
                        number_format($row->valor_compra ?? 0, 2), 
                        $row->fecha_compra ?? 'N/A'
                    ], $delimitador);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    public function catalogos()
{
    try {
        $ubicaciones = Ubicacion::with(['laboratorio.edificio'])->get();
        $estado_activos = Estado_Activo::all();

        return response()->json([
            'ubicaciones' => $ubicaciones,
            'estados' => $estado_activos
        ], 200);
    } catch (\Exception $e) {
        
        return response()->json(['error' => 'Error interno del servidor'], 500);
    }
}
}
