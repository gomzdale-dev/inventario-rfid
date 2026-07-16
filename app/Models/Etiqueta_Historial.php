<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Etiqueta_Historial extends Model
{
    
    protected $table = 'etiqueta_historial';

    public $timestamps = false;

    protected $fillable = [
        'activo_fijo_id',
        'etiqueta_rfid_id',
        'fecha_asignacion',
        'fecha_baja',
        'motivo_baja'
    ];

    // Casteo de fechas para usarlas como instancias de Carbon en PHP
    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_baja' => 'datetime',
    ];

    /**
     * Relación: El historial pertenece a un Activo Fijo
     */
    public function activo(): BelongsTo
    {
        return $this->belongsTo(Activo::class, 'activo_fijo_id', 'id_activo');
    }

    /**
     * Relación: El historial pertenece a una Etiqueta RFID
     */
    public function etiqueta(): BelongsTo
    {
        return $this->belongsTo(Etiquetas_Rfid::class, 'etiqueta_rfid_id', 'id_etiqueta');
    }
}