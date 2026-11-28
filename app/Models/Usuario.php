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

    protected $fillable = ['NOMBRE_USUARIO', 'CONTRASEÑA', 'ROL_ID'];

    protected $hidden = ['PASSWORD_HASH', 'CONTRASEÑA'];

    protected function casts(): array
    {
        return ['CONTRASEÑA' => 'hashed'];
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value !== null) {
            return $value;
        }

        $upper = strtoupper($key);
        $lower = strtolower($key);

        if ($key !== $upper && parent::getAttribute($upper) !== null) {
            return parent::getAttribute($upper);
        }

        if ($key !== $lower && parent::getAttribute($lower) !== null) {
            return parent::getAttribute($lower);
        }

        return null;
    }

    public function getAuthPassword(): string
    {
        return (string) (
            $this->getAttribute('CONTRASEÑA')
            ?? ''
        );
    }

    public function getAuthPasswordName(): string
    {
        return 'CONTRASEÑA';
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

    public function examenes()
    {
        return $this->hasMany(Examen::class, 'RADIOLOGO_ID');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'ADMINISTRADOR_ID');
    }
}
