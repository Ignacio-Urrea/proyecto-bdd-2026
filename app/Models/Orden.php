<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_ORDENES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['PACIENTE_ID', 'SUCURSAL_ID', 'EJECUTADA', 'FECHA_CITA', 'HORA_CITA', 'ESTADO'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_ORDENES';
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'PACIENTE_ID');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'SUCURSAL_ID');
    }

    public function examen()
    {
        return $this->hasOne(Examen::class, 'ORDEN_ID');
    }

    public function presupuesto()
    {
        return $this->hasOne(Presupuesto::class, 'ORDEN_ID');
    }
}
