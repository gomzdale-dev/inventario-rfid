<?php

namespace App\Models;

use App\Models\Etiquetas_Rfid;
use App\Models\Responsable;
use App\Models\Ubicacion;
use App\Models\Modelo;
use App\Models\Categoria;
use App\Models\Estado_Activo;
use App\Models\Movimiento;
use App\Models\Detalle_Inventario;
use App\Models\Etiqueta_Historial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Activo extends Model
{
    protected $table = 'activos';
    protected $primaryKey = 'id_activo';
    public $timestamps = false;

    protected $fillable = [
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
        return $this->belongsTo(Responsable::class, 'id_responsable', 'id');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function etiqueta()
    {
        return $this->belongsTo(Etiquetas_Rfid::class, 'id_etiqueta', 'id_etiqueta');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'id_modelo', 'id_modelo');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function estado_activos()
    {
        return $this->belongsTo(Estado_Activo::class, 'id_estado', 'id_estado');
    }

    public function detalleInventarios()
    {
        return $this->hasMany(Detalle_Inventario::class, 'id_activo', 'id_activo');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_activo', 'id_activo');
    }

    protected static function booted()
    {
        static::created(function (Activo $activo) {
            Movimiento::query()->create([
                'comentarios'      => 'Registro inicial del activo mediante sistema RFID.',
                'tipo_movimiento'  => 1,
                'fecha_movimiento' => now(),
                'id_usuario'       => Auth::id(),
                'id_activo'        => $activo->id_activo,
                'id_ubicacion'     => $activo->id_ubicacion,
            ]);

        });
    }
}
