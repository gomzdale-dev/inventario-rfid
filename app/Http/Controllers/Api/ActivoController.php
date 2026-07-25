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

        // 1. Validar tanto el código RFID como el motivo recibido desde Vue
        $validated = $request->validate([
            'codigo_rfid' => [
                'required',
                'string',
                'max:50',
                'unique:etiquetas_rfid,codigo'
            ],
            'motivo' => [
                'required',
                'string',
                'max:50'
            ]
        ]);

        $codigoRfid = trim($validated['codigo_rfid']);
        $motivoBaja = trim($validated['motivo']);

        if ($codigoRfid === '') {
            return response()->json([
                'message' => 'El código RFID no puede estar vacío.'
            ], 422);
        }

        return DB::transaction(function () use ($activo, $codigoRfid, $motivoBaja) {
            // 2. Crear la nueva etiqueta física RFID
            $etiquetaNueva = Etiquetas_Rfid::create([
                'codigo' => $codigoRfid,
                'estado' => 'A'
            ]);

            // 3. Dar de baja la asignación actual en el historial usando el motivo provisto por el usuario
            Etiqueta_Historial::where('activo_fijo_id', $activo->id_activo)
                ->whereNull('fecha_baja')
                ->update([
                    'fecha_baja'  => now(),
                    'motivo_baja' => $motivoBaja
                ]);

            // 4. Crear el nuevo registro de asignación activa en el historial
            Etiqueta_Historial::create([
                'activo_fijo_id'   => $activo->id_activo,
                'etiqueta_rfid_id' => $etiquetaNueva->id_etiqueta,
                'fecha_asignacion' => now(),
                'fecha_baja'       => null,
                'motivo_baja'      => null
            ]);

            // 5. Actualizar la relación de la etiqueta en la tabla "activos"
            $activo->update([
                'id_etiqueta' => $etiquetaNueva->id_etiqueta
            ]);

            // 6. Cargar las relaciones actualizadas para refrescar la tabla en el frontend
            $activo->load([
                'responsable',
                'ubicacion.laboratorio.edificio',
                'etiqueta'
            ]);

            return response()->json([
                'message' => 'Etiqueta RFID actualizada y cambio registrado en el historial correctamente.',
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

            // 1. Obtener o Crear la nueva Etiqueta RFID
            $etiqueta = Etiquetas_Rfid::firstOrCreate(
                ['codigo' => $validated['rfid']],
                ['estado' => 'A']
            );

            // Validar si la etiqueta ya se encuentra asignada a algún activo existente
            $etiquetaOcupada = Activo::where('id_etiqueta', $etiqueta->id_etiqueta)->exists();
            if ($etiquetaOcupada) {
                return response()->json([
                    'message' => 'La etiqueta RFID ya está asignada a otro activo.'
                ], 422);
            }

            // 2. Obtener o Crear la Ubicación correspondiente al Laboratorio seleccionado
            $ubicacion = Ubicacion::firstOrCreate(
                ['id_laboratorio' => $validated['id_laboratorio']],
                ['estado' => 'A']
            );

            // 3. Crear el Activo (Dispara automáticamente el Observer/Event de registro inicial)
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

            // 4. Crear el registro en el Historial de Etiquetas
            $etiqueta_historial = Etiqueta_Historial::create([
                'activo_fijo_id'   => $activo->id_activo,
                'etiqueta_rfid_id' => $etiqueta->id_etiqueta,
                'fecha_asignacion' => now(),
                'fecha_baja'       => null,
                'motivo_baja'      => null
            ]);

            return response()->json([
                'message' => 'Activo registrado correctamente, junto con su historial RFID y ubicación en el sistema.',
                'activo' => $activo->load(['responsable', 'ubicacion.laboratorio.edificio', 'etiqueta'])
            ], 201);
        });
    }
}