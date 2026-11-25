<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_PAGOS';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['PRESUPUESTO_ID', 'MONTO', 'METODO_PAGO'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_PAGOS';
    }

    public function presupuesto()
    {
        return $this->belongsTo(Presupuesto::class, 'PRESUPUESTO_ID');
    }
}
