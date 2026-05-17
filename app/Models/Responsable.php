<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsable extends Model
{
    protected $table = 'responsables';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
    'nombre',
    'apellido',
    'codigo_empleado',
    'fecha_ingreso',
    'usuario_ingreso',
    'fecha_modifica',
    'usuario_modifica'
    ];
}
