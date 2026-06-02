<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo',
        'prioridad',
        'origen',
        'codigo_rfid',
        'id_activo',
        'id_laboratorio',
        'id_usuario',
        'leida',
        'estado',
        'detectada_por_ia',
        'nivel_riesgo',
        'fecha_alerta'
    ];
}