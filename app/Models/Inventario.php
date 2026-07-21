<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table = 'inventarios';
    protected $primaryKey = 'id_inventario';
    public $timestamps = false;

    protected $casts = [
        'fecha_inventario' => 'datetime'
    ];

    protected $fillable = [
        'fecha_inventario',
        'id_usuario',
        'id_laboratorio'
    ];

    public function detalles()
    {
        return $this->hasMany(
            Detalle_Inventario::class,
            'id_inventario',
            'id_inventario'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function laboratorio()
    {
        return $this->belongsTo(
            Laboratorio::class,
            'id_laboratorio',
            'id_laboratorio'
        );
    }
}
