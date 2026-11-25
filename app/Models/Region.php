<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_REGIONES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['NOMBRE_REGION'];

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_REGIONES';
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class, 'REGION_ID');
    }
}
