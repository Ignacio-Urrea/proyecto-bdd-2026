<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasOracleSequence;

    protected $table = 'IMAJ_USUARIOS';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['NOMBRE_USUARIO', 'PASSWORD_HASH', 'ROL_ID'];

    protected $hidden = ['PASSWORD_HASH', 'CONTRASEÑA'];

    protected function casts(): array
    {
        return ['PASSWORD_HASH' => 'hashed'];
    }

    public function getAuthPasswordName(): string
    {
        return 'PASSWORD_HASH';
    }

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_USUARIOS';
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'ROL_ID');
    }

    public function clinicas()
    {
        return $this->hasMany(Clinica::class, 'ADMINISTRADOR_ID');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'ADMINISTRADOR_ID');
    }
}
