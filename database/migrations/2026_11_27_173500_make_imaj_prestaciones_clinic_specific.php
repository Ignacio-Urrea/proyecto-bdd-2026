<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columnExists = DB::table('USER_TAB_COLUMNS')
            ->where('TABLE_NAME', 'IMAJ_PRESTACIONES')
            ->where('COLUMN_NAME', 'CLINICA_ID')
            ->exists();

        if (! $columnExists) {
            if (DB::table('IMAJ_PRESTACIONES')->exists()) {
                throw new RuntimeException(
                    'Asigne cada prestación existente a una clínica antes de agregar CLINICA_ID.',
                );
            }

            DB::statement('ALTER TABLE IMAJ_PRESTACIONES ADD (CLINICA_ID NUMBER NOT NULL)');
        } else {
            $unassignedRows = DB::table('IMAJ_PRESTACIONES')
                ->whereNull('CLINICA_ID')
                ->exists();

            if ($unassignedRows) {
                throw new RuntimeException(
                    'Asigne cada prestación existente a una clínica antes de continuar la migración.',
                );
            }

            DB::statement('ALTER TABLE IMAJ_PRESTACIONES MODIFY (CLINICA_ID NOT NULL)');
        }

        $constraintExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_PRESTACIONES')
            ->where('CONSTRAINT_NAME', 'FK_IMAJ_PRESTACIONES_CLINICAS')
            ->exists();

        if (! $constraintExists) {
            DB::statement(
                'ALTER TABLE IMAJ_PRESTACIONES ADD CONSTRAINT FK_IMAJ_PRESTACIONES_CLINICAS '.
                'FOREIGN KEY (CLINICA_ID) REFERENCES IMAJ_CLINICAS (ID)',
            );
        }

        $indexExists = DB::table('USER_INDEXES')
            ->where('TABLE_NAME', 'IMAJ_PRESTACIONES')
            ->where('INDEX_NAME', 'IDX_IMAJ_PREST_CLINICA')
            ->exists();

        if (! $indexExists) {
            DB::statement('CREATE INDEX IDX_IMAJ_PREST_CLINICA ON IMAJ_PRESTACIONES (CLINICA_ID)');
        }

        $packageFile = database_path('oracle/IMAJ_APP_PKG.sql');

        if (! is_file($packageFile)) {
            throw new RuntimeException('No se encontró el archivo database/oracle/IMAJ_APP_PKG.sql.');
        }

        $packageSql = file_get_contents($packageFile);

        if ($packageSql === false) {
            throw new RuntimeException('No se pudo leer database/oracle/IMAJ_APP_PKG.sql.');
        }

        $statements = preg_split('/^\s*\/\s*$/m', $packageSql) ?: [];

        foreach ($statements as $statement) {
            if (trim($statement) !== '') {
                DB::unprepared(trim($statement));
            }
        }
    }

    public function down(): void
    {
        throw new RuntimeException(
            'No se revierte automáticamente la asociación de prestaciones con clínicas.',
        );
    }
};
