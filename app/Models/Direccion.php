<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_DIRECCIONES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['CIUDAD_ID', 'CALLE'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_DIRECCIONES';
    }

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'CIUDAD_ID');
    }

    public function clinicas()
    {
        return $this->hasMany(Clinica::class, 'DIRECCION_ID');
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'DIRECCION_ID');
    }
}
