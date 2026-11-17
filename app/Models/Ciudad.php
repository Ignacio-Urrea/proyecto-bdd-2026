<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_CIUDADES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['REGION_ID', 'NOMBRE_CIUDAD'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_CIUDADES';
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'REGION_ID');
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class, 'CIUDAD_ID');
    }
}
