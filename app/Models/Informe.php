<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_INFORMES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['CUERPO', 'ADENDUM', 'TITULO', 'RUTA', 'EXAMEN_ID'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_INFORMES';
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'EXAMEN_ID');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'INFORME_ID');
    }
}
