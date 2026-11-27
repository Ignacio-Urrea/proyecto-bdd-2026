<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columnExists = DB::table('USER_TAB_COLUMNS')
            ->where('TABLE_NAME', 'IMAJ_PACIENTES')
            ->where('COLUMN_NAME', 'SUCURSAL_ID')
            ->exists();

        if (! $columnExists) {
            DB::statement('ALTER TABLE IMAJ_PACIENTES ADD (SUCURSAL_ID NUMBER)');
        }

        $clinicColumnExists = DB::table('USER_TAB_COLUMNS')
            ->where('TABLE_NAME', 'IMAJ_PACIENTES')
            ->where('COLUMN_NAME', 'CLINICA_ID')
            ->exists();

        if ($clinicColumnExists) {
            DB::statement(
                'UPDATE IMAJ_PACIENTES P SET SUCURSAL_ID = ('.
                'SELECT MIN(O.SUCURSAL_ID) FROM IMAJ_ORDENES O '.
                'JOIN IMAJ_SUCURSALES S ON S.ID = O.SUCURSAL_ID '.
                'WHERE O.PACIENTE_ID = P.ID AND S.CLINICA_ID = P.CLINICA_ID '.
                'HAVING COUNT(DISTINCT O.SUCURSAL_ID) = 1) '.
                'WHERE P.SUCURSAL_ID IS NULL '.
                'AND EXISTS (SELECT 1 FROM IMAJ_ORDENES O '.
                'JOIN IMAJ_SUCURSALES S ON S.ID = O.SUCURSAL_ID '.
                'WHERE O.PACIENTE_ID = P.ID AND S.CLINICA_ID = P.CLINICA_ID)',
            );

            DB::statement(
                'UPDATE IMAJ_PACIENTES P SET SUCURSAL_ID = ('.
                'SELECT MIN(S.ID) FROM IMAJ_SUCURSALES S '.
                'WHERE S.CLINICA_ID = P.CLINICA_ID '.
                'HAVING COUNT(*) = 1) '.
                'WHERE P.SUCURSAL_ID IS NULL',
            );
        }

        $unassignedPatients = DB::table('IMAJ_PACIENTES')->whereNull('SUCURSAL_ID')->count();

        if ($unassignedPatients > 0) {
            throw new RuntimeException(
                "{$unassignedPatients} paciente(s) no tienen una sucursal única deducible. ".
                'Asigna SUCURSAL_ID manualmente a esos registros y vuelve a ejecutar php artisan migrate.',
            );
        }

        if ($clinicColumnExists) {
            $clinicMismatches = DB::table('IMAJ_PACIENTES as p')
                ->join('IMAJ_SUCURSALES as s', 's.ID', '=', 'p.SUCURSAL_ID')
                ->whereColumn('s.CLINICA_ID', '<>', 'p.CLINICA_ID')
                ->count();

            if ($clinicMismatches > 0) {
                throw new RuntimeException(
                    "{$clinicMismatches} paciente(s) tienen una sucursal que no corresponde a su clínica actual. ".
                    'Corrige SUCURSAL_ID manualmente y vuelve a ejecutar php artisan migrate.',
                );
            }
        }

        $clinicForeignKeyExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_PACIENTES')
            ->where('CONSTRAINT_NAME', 'FK_IMAJ_PACIENTES_CLINICA')
            ->exists();

        if ($clinicForeignKeyExists) {
            DB::statement('ALTER TABLE IMAJ_PACIENTES DROP CONSTRAINT FK_IMAJ_PACIENTES_CLINICA');
        }

        DB::statement('ALTER TABLE IMAJ_PACIENTES MODIFY (SUCURSAL_ID NOT NULL)');

        $branchForeignKeyExists = DB::table('USER_CONSTRAINTS')
            ->where('TABLE_NAME', 'IMAJ_PACIENTES')
            ->where('CONSTRAINT_NAME', 'FK_IMAJ_PACIENTES_SUCURSAL')
            ->exists();

        if (! $branchForeignKeyExists) {
            DB::statement(
                'ALTER TABLE IMAJ_PACIENTES ADD CONSTRAINT FK_IMAJ_PACIENTES_SUCURSAL '.
                'FOREIGN KEY (SUCURSAL_ID) REFERENCES IMAJ_SUCURSALES (ID)',
            );
        }

        $indexExists = DB::table('USER_INDEXES')
            ->where('TABLE_NAME', 'IMAJ_PACIENTES')
            ->where('INDEX_NAME', 'IDX_IMAJ_PACIENTES_SUCURSAL')
            ->exists();

        if (! $indexExists) {
            DB::statement('CREATE INDEX IDX_IMAJ_PACIENTES_SUCURSAL ON IMAJ_PACIENTES (SUCURSAL_ID)');
        }

        if ($clinicColumnExists) {
            DB::statement('ALTER TABLE IMAJ_PACIENTES DROP COLUMN CLINICA_ID');
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
            'No se revierte automáticamente la relación paciente-sucursal porque la migración puede resolver asignaciones históricas ambiguas.',
        );
    }
};
