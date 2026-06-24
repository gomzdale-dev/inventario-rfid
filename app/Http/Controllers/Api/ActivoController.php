<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Estado_Activo;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Models\Etiquetas_Rfid;
use App\Models\Modelo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
                Rule::unique('activos', 'id_etiqueta')->ignore(
                    $activo->id_activo,
                    'id_activo'
                ),
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
                'max:50',
                'unique:etiquetas_rfid,codigo'
            ]
        ]);

        $codigoRfid = trim($validated['codigo_rfid']);

        if ($codigoRfid === '') {
            return response()->json([
                'message' => 'El código RFID no puede estar vacío.'
            ], 422);
        }

        return DB::transaction(function () use ($activo, $codigoRfid) {
            $etiqueta = Etiquetas_Rfid::create([
                'codigo' => $codigoRfid,
                'estado' => 'A'
            ]);

            $activo->update([
                'id_etiqueta' => $etiqueta->id_etiqueta
            ]);

            $activo->load([
                'responsable',
                'ubicacion.laboratorio.edificio',
                'etiqueta'
            ]);

            return response()->json([
                'message' => 'Etiqueta RFID actualizada correctamente',
                'activo' => $activo
            ]);
        });
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

        $validated = $request->validate([
            'nombre_activo' => 'required|string|max:50',
            'serie' => 'required|string|max:50|unique:activos,serie',
            'valor_compra' => 'required|numeric',
            'fecha_compra' => 'required|date',
            'valor_actual' => 'nullable|numeric',
            'vida_util' => 'required|integer',
            'depreciacion_anual' => 'nullable|numeric',
            'id_laboratorio' => 'required|exists:laboratorios,id_laboratorio',
            'id_categoria' => 'required|exists:categorias,id_categoria',
            'id_modelo' => 'required|exists:modelos,id_modelo',
            'id_estado' => 'nullable|exists:estado_activos,id_estado',
            'id_responsable' => 'nullable|exists:responsables,id',
            'rfid' => 'required|string|max:50',
        ]);

        $etiqueta = Etiquetas_Rfid::firstOrCreate(
            ['codigo' => $validated['rfid']],
            ['estado' => 'A']
        );

        $ubicacion = Ubicacion::firstOrCreate(
            ['id_laboratorio' => $validated['id_laboratorio']],
            ['estado' => 'A']
        );

        $activo = Activo::create([
            'nombre_activo' => $validated['nombre_activo'],
            'serie' => $validated['serie'],
            'valor_compra' => $validated['valor_compra'],
            'fecha_compra' => $validated['fecha_compra'],
            'valor_actual' => $validated['valor_actual'] ?? 0,
            'vida_util' => $validated['vida_util'],
            'depreciacion_anual' => $validated['depreciacion_anual'] ?? 0,
            'id_etiqueta' => $etiqueta->id_etiqueta,
            'id_categoria' => $validated['id_categoria'],
            'id_modelo' => $validated['id_modelo'],
            'id_ubicacion' => $ubicacion->id_ubicacion,
            'id_estado' => $validated['id_estado'] ?? 1,
            'id_responsable' => $validated['id_responsable'] ?? null
        ]);


        return response()->json([
            'message' => 'Activo registrado correctamente',
            'activo' => $activo->load(['responsable', 'ubicacion.laboratorio.edificio', 'etiqueta'])
        ], 201);
        });
    }

}