<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_Inventario extends Model
{
    protected $table = 'detalle_inventarios';
    protected $primaryKey = 'id_detalle';
    public $timestamps = false;

    protected $casts = [
        'encontrado' => 'boolean',
        'fecha_lectura' => 'datetime'
    ];

    protected $fillable = [
        'observaciones',
        'cantidad',
        'encontrado',
        'fecha_lectura',
        'id_inventario',
        'id_activo'
    ];

    public function inventario()
    {
        return $this->belongsTo(
            Inventario::class,
            'id_inventario',
            'id_inventario'
        );
    }

    public function activo()
    {
        return $this->belongsTo(
            Activo::class,
            'id_activo',
            'id_activo'
        );
    }
}
