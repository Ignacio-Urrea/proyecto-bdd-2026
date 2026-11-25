<?php

namespace App\Models;

use App\Models\Concerns\HasOracleSequence;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasOracleSequence;

    protected $table = 'IMAJ_ROLES';

    protected $primaryKey = 'ID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['NOMBRE_ROL'];

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($value !== null) {
            return $value;
        }

        $upper = strtoupper($key);
        $lower = strtolower($key);

        if ($key !== $upper && parent::getAttribute($upper) !== null) {
            return parent::getAttribute($upper);
        }

        if ($key !== $lower && parent::getAttribute($lower) !== null) {
            return parent::getAttribute($lower);
        }

        return null;
    }

    protected function oracleSequence(): string
    {
        return 'SEQ_IMAJ_ROLES';
    }

    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'ROL_ID');
    }
}
