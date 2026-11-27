<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::table('USER_TAB_COLUMNS')
            ->where('TABLE_NAME', 'IMAJ_USUARIOS')
            ->pluck('COLUMN_NAME')
            ->map(static fn (string $column): string => strtoupper($column))
            ->all();

        if (! in_array('CONTRASEÑA', $columns, true)) {
            throw new RuntimeException('No existe la columna CONTRASEÑA en IMAJ_USUARIOS.');
        }

        $hasPasswordHash = in_array('PASSWORD_HASH', $columns, true);

        DB::statement('ALTER TABLE IMAJ_USUARIOS MODIFY (CONTRASEÑA VARCHAR2(255))');

        $query = DB::table('IMAJ_USUARIOS')->select('ID', 'CONTRASEÑA');

        if ($hasPasswordHash) {
            $query->addSelect('PASSWORD_HASH');
        }

        foreach ($query->orderBy('ID')->cursor() as $user) {
            $storedPassword = $hasPasswordHash
                ? ($user->PASSWORD_HASH ?: $user->{'CONTRASEÑA'})
                : $user->{'CONTRASEÑA'};

            if ($storedPassword === null) {
                continue;
            }

            $storedPassword = (string) $storedPassword;

            if (! Hash::isHashed($storedPassword)) {
                $storedPassword = Hash::make($storedPassword);
            }

            if ($user->{'CONTRASEÑA'} !== $storedPassword) {
                DB::table('IMAJ_USUARIOS')
                    ->where('ID', $user->ID)
                    ->update(['CONTRASEÑA' => $storedPassword]);
            }
        }

        if ($hasPasswordHash) {
            DB::statement('ALTER TABLE IMAJ_USUARIOS DROP COLUMN PASSWORD_HASH');
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
            'No se revierte automáticamente la consolidación de contraseñas para evitar restaurar datos inseguros.',
        );
    }
};
