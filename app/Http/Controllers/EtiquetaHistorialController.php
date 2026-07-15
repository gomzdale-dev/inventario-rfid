<?php

namespace App\Http\Controllers;

use App\Models\Etiqueta_Historial;
use App\Models\Activo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EtiquetaHistorialController extends Controller
{
    /**
     * Obtener el historial completo de etiquetas de un activo específico.
     * Útil para mostrar una línea de tiempo o tabla en Vue.js.
     */
    public function obtenerHistorialPorActivo($id_activo)
    {
        $historial = Etiqueta_Historial::with(['etiqueta'])
            ->where('activo_fijo_id', $id_activo)
            ->orderBy('fecha_asignacion', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $historial
        ], 200);
    }

    
    public function reemplazarEtiqueta(Request $request)
    {
        $request->validate([
            'activo_fijo_id'   => 'required|exists:activos,id_activo',
            'nueva_etiqueta_id' => 'required|exists:etiquetas_rfid,id_etiqueta',
            'motivo_baja'       => 'required|string|in:perdida,dañada,reemplazo',
        ]);

        try {
            DB::beginTransaction();

            $ahora = Carbon::now();

            // 1. Dar de baja la etiqueta que esté activa actualmente en el historial
            Etiqueta_Historial::where('activo_fijo_id', $request->activo_fijo_id)
                ->whereNull('fecha_baja')
                ->update([
                    'fecha_baja' => $ahora,
                    'motivo_baja' => $request->motivo_baja
                ]);

            // 2. Crear el nuevo registro de asignación en el historial
            $nuevoHistorial = Etiqueta_Historial::create([
                'activo_fijo_id' => $request->activo_fijo_id,
                'etiqueta_rfid_id' => $request->nueva_etiqueta_id,
                'fecha_asignacion' => $ahora,
            ]);

            // 3. Actualizar la relación directa de la etiqueta en la tabla "activos"
            $activo = Activo::find($request->activo_fijo_id);
            $activo->id_etiqueta = $request->nueva_etiqueta_id;
            $activo->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '¡Etiqueta reemplazada e historial actualizado correctamente!',
                'data' => $nuevoHistorial
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Hubo un error al procesar el reemplazo de la etiqueta.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}