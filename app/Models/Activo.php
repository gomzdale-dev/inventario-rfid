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
use Carbon\Carbon;
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
    public function getDepreciacionAnualAttribute()
    {
        // Fórmula: Valor de compra / Vida útil
        if ($this->vida_util > 0) {
            return round($this->valor_compra / $this->vida_util, 2);
        }
        return 0.00;
    }

    /**
     * Accessor para calcular el valor actual en tiempo real (para consultas y reportes).
     */
    public function getValorActualAttribute()
    {
        $fechaCompra = Carbon::parse($this->fecha_compra);
        $fechaHoy = Carbon::now();

        // 1. Calcular los años transcurridos con precisión decimal
        $anosTranscurridos = $fechaCompra->diffInDays($fechaHoy) / 365.25;

        // Si la fecha de compra es futura por error, se devuelve el valor original
        if ($anosTranscurridos < 0) {
            return round($this->valor_compra, 2);
        }

        // 2. Si ya superó su vida útil, su valor contable actual es $0.00
        if ($anosTranscurridos >= $this->vida_util) {
            return 0.00;
        }

        // 3. Restar la depreciación acumulada al valor de compra original
        $depreciacionAnual = $this->depreciacion_anual; // Llama al accessor de arriba
        $depreciacionAcumulada = $depreciacionAnual * $anosTranscurridos;
        $valorCalculado = $this->valor_compra - $depreciacionAcumulada;

        return round($valorCalculado, 2);
    }

    /**
     * Indicarle a Laravel que adjunte estos campos calculados siempre que el modelo se convierta a JSON (para Vue).
     */
    protected $appends = ['depreciacion_anual', 'valor_actual'];
}
