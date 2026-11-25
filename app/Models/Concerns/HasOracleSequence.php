<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait HasOracleSequence
{
    protected static function bootHasOracleSequence(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute($model->getKeyName()) !== null) {
                return;
            }

            $sequence = $model->oracleSequence();
            $row = DB::selectOne("SELECT {$sequence}.NEXTVAL AS ID FROM DUAL");
            $id = $row->id ?? $row->ID ?? null;

            if ($id === null) {
                throw new RuntimeException("No se pudo obtener el siguiente valor de {$sequence}.");
            }

            $model->setAttribute($model->getKeyName(), $id);
        });
    }

    abstract protected function oracleSequence(): string;
}
