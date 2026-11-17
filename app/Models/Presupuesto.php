<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_PRESUPUESTOS';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['ESTADO', 'ORDEN_ID'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_PRESUPUESTOS';
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'ORDEN_ID');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'PRESUPUESTO_ID');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePrestacion::class, 'PRESUPUESTO_ID');
    }
}
