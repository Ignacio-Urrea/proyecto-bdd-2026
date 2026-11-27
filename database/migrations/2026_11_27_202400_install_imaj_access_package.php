<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->installPackage('IMAJ_APP_PKG.sql');
        $this->installPackage('IMAJ_ACCESS_PKG.sql');
    }

    public function down(): void
    {
        DB::unprepared('DROP PACKAGE IMAJ_ACCESS_PKG');
    }

    private function installPackage(string $fileName): void
    {
        $packageFile = database_path('oracle/'.$fileName);

        if (! is_file($packageFile)) {
            throw new RuntimeException("No se encontró el archivo database/oracle/{$fileName}.");
        }

        $statements = preg_split('/^\s*\/\s*$/m', file_get_contents($packageFile)) ?: [];

        foreach ($statements as $statement) {
            if (trim($statement) !== '') {
                DB::unprepared(trim($statement));
            }
        }
    }
};
