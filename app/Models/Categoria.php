<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
     public $timestamps = false; 

     protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nombre_categoria',
        'estado',
        'fecha_ingreso',
        'usuario_ingreso',
        'fecha_modifica',
        'usuario_modifica'
    ];
}
