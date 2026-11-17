<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Clinica extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_CLINICAS';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['ADMINISTRADOR_ID', 'DIRECCION_ID', 'NOMBRE_CLINICA', 'CORREO_CLINICA'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_CLINICAS';
    }

    public function administrador()
    {
        return $this->belongsTo(Usuario::class, 'ADMINISTRADOR_ID');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'DIRECCION_ID');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class, 'CLINICA_ID');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'CLINICA_ID');
    }
}
