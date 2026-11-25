<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_SUCURSALES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['ADMINISTRADOR_ID', 'CLINICA_ID', 'CORREO_SUCURSAL', 'DIRECCION_ID'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_SUCURSALES';
    }

    public function administrador()
    {
        return $this->belongsTo(Usuario::class, 'ADMINISTRADOR_ID');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class, 'CLINICA_ID');
    }

    public function direccion()
    {
        return $this->belongsTo(Direccion::class, 'DIRECCION_ID');
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'SUCURSAL_ID');
    }
}
