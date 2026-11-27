<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columnExists = DB::table('USER_TAB_COLUMNS')
            ->where('TABLE_NAME', 'IMAJ_EXAMENES')
            ->where('COLUMN_NAME', 'RADIOLOGO_ID')
            ->exists();

        if (! $columnExists) {
            DB::statement('ALTER TABLE IMAJ_EXAMENES ADD (RADIOLOGO_ID NUMBER)');
        }

        $constraintExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_EXAMENES')
            ->where('CONSTRAINT_NAME', 'FK_IMAJ_EXAMENES_RADIOLOGO')
            ->exists();

        if (! $constraintExists) {
            DB::statement(
                'ALTER TABLE IMAJ_EXAMENES ADD CONSTRAINT FK_IMAJ_EXAMENES_RADIOLOGO '.
                'FOREIGN KEY (RADIOLOGO_ID) REFERENCES IMAJ_USUARIOS (ID)',
            );
        }

        $indexExists = DB::table('USER_INDEXES')
            ->where('TABLE_NAME', 'IMAJ_EXAMENES')
            ->where('INDEX_NAME', 'IDX_IMAJ_EXAMENES_RADIOLOGO')
            ->exists();

        if (! $indexExists) {
            DB::statement('CREATE INDEX IDX_IMAJ_EXAMENES_RADIOLOGO ON IMAJ_EXAMENES (RADIOLOGO_ID)');
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
            'No se revierte automáticamente RADIOLOGO_ID para preservar asignaciones y evitar invalidar IMAJ_APP_PKG.',
        );
    }
};
