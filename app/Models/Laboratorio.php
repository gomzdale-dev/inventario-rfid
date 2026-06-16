<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratorio extends Model
{
    protected $table = 'laboratorios';
    protected $primaryKey = 'id_laboratorio';
    public $timestamps = false;

    protected $fillable = [
        'nombre_laboratorio',
        'id_edificio',
        'estado'
    ];

    public function edificio()
    {
        return $this->belongsTo(Edificio::class, 'id_edificio', 'id_edificio');
    }
}
