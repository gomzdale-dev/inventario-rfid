<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\TipoUsuario;
class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nombre_usuario',
        'correo',
        'password',
        'estado',
        'fecha_ingreso',
        'usuario_ingreso',
        'fecha_modifica',
        'usuario_modifica',
        'id_tipo'
    ];

    protected $hidden = [
        'password'
    ];

    public function tipoUsuario()
{
    return $this->belongsTo(TipoUsuario::class, 'id_tipo');
}
}
