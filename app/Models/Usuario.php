<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\TipoUsuario;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Usuario extends Authenticatable
{
    use HasApiTokens;
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
    protected $appends = ['rol_nombre'];

    public function tipoUsuario()
{
    return $this->belongsTo(TipoUsuario::class, 'id_tipo');
}
    protected function rolNombre(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tipoUsuario?->nombre_tipo ?? 'Sin Rol',
        );
    }
}
