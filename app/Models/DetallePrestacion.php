<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class DetallePrestacion extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_DETALLE_PRESTACIONES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['PRESUPUESTO_ID', 'PRESTACION_ID', 'PRECIO'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_DETALLE_PRESTACIONES';
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'PRESUPUESTO_ID');
    }

    public function prestacion()
    {
        return $this->belongsTo(Prestacion::class, 'PRESTACION_ID');
    }
}
