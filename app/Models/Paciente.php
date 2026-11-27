<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_PACIENTES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['SUCURSAL_ID', 'FECHA_NACIMIENTO', 'NOMBRE', 'APELLIDO_P', 'APELLIDO_M', 'RUT', 'TELEFONO'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_PACIENTES';
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'SUCURSAL_ID');
    }

    public function ordenes()
    {
        return $this->hasMany(Orden::class, 'PACIENTE_ID');
    }
}
