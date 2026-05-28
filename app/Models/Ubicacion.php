<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $table='ubicaciones';

    protected $primaryKey='id_ubicacion';

    public $timestamps=false;
}