<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    //
    protected $table='inventarios';

    protected $primaryKey='id_inventario';

    public $timestamps=false;
    protected $fillable = [
        'fecha_inventario',
        'id_usuario'
    ];

    public function detalles()
    {
        return $this->hasMany(Detalle_Inventario::class,'id_inventario','id_inventario');
    }
     public function detallesInventario()
    {
        return $this->hasMany(Detalle_Inventario::class,'id_activo','id_activo');
    }
}
