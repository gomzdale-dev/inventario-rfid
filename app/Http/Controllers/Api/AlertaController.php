<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function index()
    {
        return response()->json(
            Alerta::orderBy('fecha_alerta', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensaje' => 'required|string',
            'tipo' => 'nullable|string|max:50',
            'prioridad' => 'nullable|string|max:50',
            'origen' => 'nullable|string|max:100',
            'codigo_rfid' => 'nullable|string|max:100',
            'id_activo' => 'nullable|integer',
            'id_laboratorio' => 'nullable|integer',
            'nivel_riesgo' => 'nullable|integer'
        ]);

        $alerta = Alerta::create([
            'titulo' => $request->titulo,
            'mensaje' => $request->mensaje,
            'tipo' => $request->tipo ?? 'sistema',
            'prioridad' => $request->prioridad ?? 'info',
            'origen' => $request->origen ?? 'manual',
            'codigo_rfid' => $request->codigo_rfid,
            'id_activo' => $request->id_activo,
            'id_laboratorio' => $request->id_laboratorio,
            'id_usuario' => $request->user()?->id_usuario,
            'detectada_por_ia' => $request->detectada_por_ia ?? false,
            'nivel_riesgo' => $request->nivel_riesgo,
            'leida' => false,
            'estado' => 'activa'
        ]);

        return response()->json([
            'message' => 'Alerta registrada correctamente',
            'data' => $alerta
        ], 201);
    }

    public function marcarLeida($id)
    {
        $alerta = Alerta::findOrFail($id);
        $alerta->leida = true;
        $alerta->save();

        return response()->json([
            'message' => 'Alerta marcada como leída',
            'data' => $alerta
        ]);
    }

    public function marcarTodas()
{
    Alerta::query()
        ->where('leida', false)
        ->update(['leida' => true]);

    return response()->json([
        'message' => 'Todas las alertas fueron marcadas como leídas'
    ]);
}

    public function destroy($id)
    {
        $alerta = Alerta::findOrFail($id);
        $alerta->delete();

        return response()->json([
            'message' => 'Alerta eliminada correctamente'
        ]);
    }

    public function simularRfid()
    {
        $alerta = Alerta::create([
            'titulo' => 'Alerta crítica RFID',
            'mensaje' => 'Activo detectado fuera del laboratorio autorizado.',
            'tipo' => 'rfid',
            'prioridad' => 'critical',
            'origen' => 'Simulador RFID',
            'codigo_rfid' => 'RFID-2847',
            'leida' => false,
            'estado' => 'activa',
            'detectada_por_ia' => false,
            'nivel_riesgo' => 85
        ]);

        return response()->json([
            'message' => 'Alerta RFID simulada correctamente',
            'data' => $alerta
        ], 201);
    }

    public function simularIa()
    {
        $alerta = Alerta::create([
            'titulo' => 'IA detectó comportamiento inusual',
            'mensaje' => 'El activo ACT-203 fue detectado varias veces fuera del horario permitido.',
            'tipo' => 'ia',
            'prioridad' => 'critical',
            'origen' => 'Motor de análisis IA',
            'codigo_rfid' => 'RFID-203',
            'leida' => false,
            'estado' => 'activa',
            'detectada_por_ia' => true,
            'nivel_riesgo' => 92
        ]);

        return response()->json([
            'message' => 'Alerta IA simulada correctamente',
            'data' => $alerta
        ], 201);
    }
}