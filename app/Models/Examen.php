<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Examen extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_EXAMENES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['ORDEN_ID', 'URGENCIA', 'FECHA_SOLICITUD', 'ESTADO'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_EXAMENES';
    }

    public function orden()
    {
        return $this->belongsTo(Orden::class, 'ORDEN_ID');
    }

    public function informes()
    {
        return $this->hasMany(Informe::class, 'EXAMEN_ID');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'EXAMEN_ID');
    }
}
