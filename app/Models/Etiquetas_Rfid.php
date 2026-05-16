<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etiquetas_Rfid extends Model
{
    protected $table='etiquetas_rfid';

    protected $primaryKey='id_etiqueta';

    public $timestamps=false;
}