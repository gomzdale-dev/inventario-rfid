<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'tipo_usuarios';

    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo',
        'descripcion',
        'estado',
        'fecha_ingreso',
        'usuario_ingreso',
        'fecha_modifica',
        'usuario_modifica'
    ];
}
