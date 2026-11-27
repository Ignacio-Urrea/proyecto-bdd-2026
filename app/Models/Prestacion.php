<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Prestacion extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_PRESTACIONES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['CLINICA_ID', 'NOMBRE', 'PRECIO_BASE', 'HABILITADA'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_PRESTACIONES';
    }

    public function detalles()
    {
        return $this->hasMany(DetallePrestacion::class, 'PRESTACION_ID');
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class, 'CLINICA_ID');
    }
}
