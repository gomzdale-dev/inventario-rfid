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
    'estado'
    ];
}
