<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_Movimiento extends Model
{
    protected $table = 'tipo_movimientos';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nombre_movimiento',
        'estado'
    ];
}
