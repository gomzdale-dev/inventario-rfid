<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado_Activo extends Model
{
    //
    protected $table = 'estado_activos';
    protected $primaryKey = 'id_estado';
    public $timestamps = false;

    protected $fillable = [
        'nombre_estado',
        'descripcion',
        'estado'
    ];
}
