<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_IMAGENES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['TAMANIO', 'NOMBRE', 'TIPO', 'RUTA', 'EXAMEN_ID', 'INFORME_ID'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_IMAGENES';
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'EXAMEN_ID');
    }

    public function informe()
    {
        return $this->belongsTo(Informe::class, 'INFORME_ID');
    }
}
