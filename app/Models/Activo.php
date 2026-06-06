<?php

namespace App\Models;
use App\Models\Etiquetas_Rfid;

use Illuminate\Database\Eloquent\Model;

class Activo extends Model
{
    protected $table='activos';

    protected $primaryKey='id_activo';

    public $timestamps=false;

    protected $fillable=[
        'nombre_activo',
        'serie',
        'valor_compra',
        'fecha_compra',
        'valor_actual',
        'vida_util',
        'depreciacion_anual',
        'id_etiqueta',
        'id_categoria',
        'id_modelo',
        'id_ubicacion',
        'id_estado',
        'id_responsable'
    ];

    public function responsable()
    {
        return $this->belongsTo(
            Responsable::class,
            'id_responsable'
        );
    }

    public function ubicacion()
    {
        return $this->belongsTo(
            Ubicacion::class,
            'id_ubicacion'
        );
    }

    public function etiqueta()
    {
        return $this->belongsTo(
            Etiquetas_Rfid::class,
            'id_etiqueta'
        );
    }
}