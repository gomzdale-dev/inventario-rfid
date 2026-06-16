<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Modelo extends Model
{
    protected $table = 'modelos';
    protected $primaryKey = 'id_modelo';
    public $timestamps = false;

    protected $fillable = [
        'nombre_modelo',
        'estado',
        'id_marca'
    ];

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca' ,'id_marca');
    }
}
