<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table='ubicaciones';

    protected $primaryKey='id_ubicacion';

    public $timestamps=false;
    protected $fillable = [
    'id_laboratorio',
    'estado'];

    public function laboratorio()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratorio', 'id_laboratorio');
    }
}