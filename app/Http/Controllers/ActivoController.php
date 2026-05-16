<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activo;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Models\Etiquetas_Rfid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivoController extends Controller
{
    public function index()
    {
        $activos = Activo::with([
            'responsable',
            'ubicacion',
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
            'ubicacion',
            'etiqueta'
        ]);

        return response()->json([
            'message' => 'Asignaciones del activo actualizadas correctamente',
            'activo' => $activo
        ]);
    }
}
