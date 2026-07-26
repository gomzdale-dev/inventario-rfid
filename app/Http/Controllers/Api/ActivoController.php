<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Estado_Activo;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Models\Etiquetas_Rfid;
use App\Models\Etiqueta_Historial;
use App\Models\Movimiento;
use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ActivoController extends Controller
{
    public function index()
    {
        $activos = Activo::with([
            'responsable',
            'ubicacion.laboratorio.edificio',
            'etiqueta'
        ])->get();

        return response()->json($activos);
    }

    public function catalogos()
    {
        return response()->json([
            'responsables' => Responsable::select(
                'id',
                'nombre',
                'apellido',
                'codigo_empleado'
            )->get(),

            'ubicaciones' => Ubicacion::select(
                'id_ubicacion',
                'id_laboratorio'
            )->get(),

            'etiquetas' => Etiquetas_Rfid::select(
                'id_etiqueta',
                'codigo'
            )->get(),

            'modelos' => Modelo::select(
                'id_modelo',
                'nombre_modelo'
            )->get(),

            'estado_activos' => Estado_Activo::select(
                'id_estado',
                'nombre_estado'
            )->get()
        ]);
    }

    public function actualizarAsignaciones(Request $request, $id)
    {
        $activo = Activo::findOrFail($id);

        $validated = $request->validate([
            'id_responsable' => [
                'nullable',
                'exists:responsables,id'
            ],
            'id_ubicacion' => [
                'required',
                'exists:ubicaciones,id_ubicacion'
            ],
            'id_etiqueta' => [
                'required',
                Rule::unique('activos', 'id_etiqueta')->ignore($activo->id_activo, 'id_activo'),
                'exists:etiquetas_rfid,id_etiqueta'
            ]
        ]);

        $activo->update($validated);

        $activo->load([
            'responsable',
            'ubicacion.laboratorio.edificio',
            'etiqueta'
        ]);

        return response()->json([
            'message' => 'Asignaciones del activo actualizadas correctamente',
            'activo' => $activo
        ]);
    }

    public function actualizarEtiquetaRfid(Request $request, $id)
    {
        $activo = Activo::findOrFail($id);

        $validated = $request->validate([
            'codigo_rfid' => [
                'required',
                'string',
                'max:50'
            ],
            'motivo' => [
                'required',
                'string',
                'max:50'
            ]
        ]);

        $codigoRfid = strtoupper(trim($validated['codigo_rfid']));
        $motivoBaja = trim($validated['motivo']);

        if ($codigoRfid === '') {
            return response()->json([
                'message' => 'El código RFID no puede estar vacío.'
            ], 422);
        }

        return DB::transaction(function () use ($activo, $codigoRfid, $motivoBaja) {
            $etiquetaNueva = Etiquetas_Rfid::firstOrCreate(
                [
                    'codigo' => $codigoRfid
                ],
                [
                    'estado' => 'A'
                ]
            );

            $activoQueLaUsa = Activo::where(
                'id_etiqueta',
                $etiquetaNueva->id_etiqueta
            )
                ->where(
                    'id_activo',
                    '!=',
                    $activo->id_activo
                )
                ->first();

            if ($activoQueLaUsa) {
                return response()->json([
                    'message' =>
                        'La etiqueta RFID ya está asignada al activo: ' .
                        $activoQueLaUsa->nombre_activo .
                        '. Escanea una etiqueta que no esté siendo utilizada.'
                ], 422);
            }

            if (
                (int) $activo->id_etiqueta ===
                (int) $etiquetaNueva->id_etiqueta
            ) {
                return response()->json([
                    'message' =>
                        'La etiqueta escaneada ya está asignada a este mismo activo.'
                ], 422);
            }

            Etiqueta_Historial::where(
                'activo_fijo_id',
                $activo->id_activo
            )
                ->whereNull('fecha_baja')
                ->update([
                    'fecha_baja' => now(),
                    'motivo_baja' => $motivoBaja
                ]);

            Etiqueta_Historial::create([
                'activo_fijo_id' => $activo->id_activo,
                'etiqueta_rfid_id' => $etiquetaNueva->id_etiqueta,
                'fecha_asignacion' => now(),
                'fecha_baja' => null,
                'motivo_baja' => null
            ]);

            $activo->update([
                'id_etiqueta' => $etiquetaNueva->id_etiqueta
            ]);

            $etiquetaNueva->update([
                'estado' => 'A'
            ]);

            $activo->load([
                'responsable',
                'ubicacion.laboratorio.edificio',
                'etiqueta'
            ]);

            return response()->json([
                'message' =>
                    'Etiqueta RFID actualizada y cambio registrado correctamente.',
                'activo' => $activo
            ]);
        });
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $validated = $request->validate([
                'nombre_activo'  => 'required|string|max:50',
                'serie'          => 'required|string|max:50|unique:activos,serie',
                'valor_compra'   => 'required|numeric',
                'fecha_compra'   => 'required|date|before_or_equal:today',
                'vida_util'      => 'required|integer|min:1|max:50',
                'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio',
                'id_categoria'   => 'required|exists:categorias,id_categoria',
                'id_modelo'      => 'required|exists:modelos,id_modelo',
                'id_estado'      => 'nullable|exists:estado_activos,id_estado',
                'id_responsable' => 'nullable|exists:responsables,id',
                'rfid'           => 'required|string|max:50',
            ]);

            $etiqueta = Etiquetas_Rfid::firstOrCreate(
                ['codigo' => $validated['rfid']],
                ['estado' => 'A']
            );

            $etiquetaOcupada = Activo::where('id_etiqueta', $etiqueta->id_etiqueta)->exists();

            if ($etiquetaOcupada) {
                return response()->json([
                    'message' => 'La etiqueta RFID ya está asignada a otro activo.'
                ], 422);
            }

            $ubicacion = Ubicacion::firstOrCreate(
                ['id_laboratorio' => $validated['id_laboratorio']],
                ['estado' => 'A']
            );

            $activo = Activo::create([
                'nombre_activo'      => $validated['nombre_activo'],
                'serie'              => $validated['serie'],
                'valor_compra'       => $validated['valor_compra'],
                'fecha_compra'       => $validated['fecha_compra'],
                'valor_actual'       => $validated['valor_actual'] ?? $validated['valor_compra'],
                'vida_util'          => $validated['vida_util'],
                'depreciacion_anual' => $validated['depreciacion_anual'] ?? 0,
                'id_etiqueta'        => $etiqueta->id_etiqueta,
                'id_categoria'       => $validated['id_categoria'],
                'id_modelo'          => $validated['id_modelo'],
                'id_ubicacion'       => $ubicacion->id_ubicacion,
                'id_estado'          => $validated['id_estado'] ?? 1,
                'id_responsable'     => $validated['id_responsable'] ?? null
            ]);

            $etiqueta_historial = Etiqueta_Historial::create([
                'activo_fijo_id'   => $activo->id_activo,
                'etiqueta_rfid_id' => $etiqueta->id_etiqueta,
                'fecha_asignacion' => now(),
                'fecha_baja'       => null,
                'motivo_baja'      => null
            ]);

            return response()->json([
                'message' => 'Activo registrado correctamente, junto con su historial RFID y ubicación en el sistema.',
                'activo' => $activo->load([
                    'responsable',
                    'ubicacion.laboratorio.edificio',
                    'etiqueta'
                ])
            ], 201);
        });
    }
}