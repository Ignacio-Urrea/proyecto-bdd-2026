<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasUnassignedRows = DB::table('IMAJ_DETALLE_PRESTACIONES')
            ->whereNull('PRESUPUESTO_ID')
            ->orWhereNull('PRESTACION_ID')
            ->exists();

        if ($hasUnassignedRows) {
            throw new RuntimeException(
                'Asigne un presupuesto y una prestación a cada detalle antes de aplicar la restricción.',
            );
        }

        $hasDuplicatePairs = DB::table('IMAJ_DETALLE_PRESTACIONES')
            ->select('PRESUPUESTO_ID', 'PRESTACION_ID')
            ->groupBy('PRESUPUESTO_ID', 'PRESTACION_ID')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicatePairs) {
            throw new RuntimeException(
                'Elimine los detalles duplicados por presupuesto y prestación antes de aplicar la restricción.',
            );
        }

        DB::statement(
            'ALTER TABLE IMAJ_DETALLE_PRESTACIONES '.
            'MODIFY (PRESUPUESTO_ID NOT NULL, PRESTACION_ID NOT NULL)',
        );

        $constraintExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_DETALLE_PRESTACIONES')
            ->where('CONSTRAINT_NAME', 'UQ_IMAJ_DET_PREST_PAIR')
            ->exists();

        if (! $constraintExists) {
            DB::statement(
                'ALTER TABLE IMAJ_DETALLE_PRESTACIONES '.
                'ADD CONSTRAINT UQ_IMAJ_DET_PREST_PAIR '.
                'UNIQUE (PRESUPUESTO_ID, PRESTACION_ID)',
            );
        }
    }

    public function down(): void
    {
        $constraintExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_DETALLE_PRESTACIONES')
            ->where('CONSTRAINT_NAME', 'UQ_IMAJ_DET_PREST_PAIR')
            ->exists();

        if ($constraintExists) {
            DB::statement(
                'ALTER TABLE IMAJ_DETALLE_PRESTACIONES '.
                'DROP CONSTRAINT UQ_IMAJ_DET_PREST_PAIR',
            );
        }

        DB::statement(
            'ALTER TABLE IMAJ_DETALLE_PRESTACIONES '.
            'MODIFY (PRESUPUESTO_ID NULL, PRESTACION_ID NULL)',
        );
    }
};
