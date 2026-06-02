<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alerta;
use Illuminate\Http\Request;

class RfidScanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'codigo_rfid' => 'required|string|max:100',
            'lector' => 'nullable|string|max:100',
            'laboratorio' => 'nullable|string|max:100',
            'evento' => 'nullable|string|max:50'
        ]);

        $evento = trim($request->evento ?? 'lectura');

        $prioridad = match ($evento) {
            'salida_no_autorizada' => 'critical',
            'fuera_horario' => 'warning',
            'lectura' => 'info',
            default => 'info'
        };

        $titulo = match ($evento) {
            'salida_no_autorizada' => 'Activo no autorizado',
            'fuera_horario' => 'Movimiento fuera de horario',
            'lectura' => 'Lectura RFID detectada',
            default => 'Evento RFID registrado'
        };

        $mensaje = match ($evento) {
    'salida_no_autorizada' => 'El activo con etiqueta ' . $request->codigo_rfid . ' fue detectado saliendo de ' . ($request->laboratorio ?? 'laboratorio no especificado') . '.',
    'fuera_horario' => 'La etiqueta ' . $request->codigo_rfid . ' fue detectada fuera del horario permitido en ' . ($request->laboratorio ?? 'laboratorio no especificado') . '.',
    'lectura' => 'Se detectó la etiqueta ' . $request->codigo_rfid . ' en el lector ' . ($request->lector ?? 'no especificado') . ' ubicado en ' . ($request->laboratorio ?? 'laboratorio no especificado') . '.',
    default => 'Se registró un evento RFID para la etiqueta ' . $request->codigo_rfid . ' en ' . ($request->laboratorio ?? 'laboratorio no especificado') . '.'
};

        $alerta = Alerta::create([
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => 'rfid',
            'prioridad' => $prioridad,
            'origen' => $request->lector ?? 'Lector RFID',
            'codigo_rfid' => $request->codigo_rfid,
            'leida' => false,
            'estado' => 'activa',
            'detectada_por_ia' => false,
            'nivel_riesgo' => $prioridad === 'critical' ? 90 : ($prioridad === 'warning' ? 65 : 30)
        ]);

        return response()->json([
            'message' => 'Lectura RFID procesada correctamente',
            'alerta' => $alerta
        ], 201);
    }
}