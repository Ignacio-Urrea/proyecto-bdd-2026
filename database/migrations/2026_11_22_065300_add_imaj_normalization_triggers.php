<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            'CREATE OR REPLACE TRIGGER TRG_IMAJ_UPPER_PACIENTES '.
            'BEFORE INSERT OR UPDATE ON IMAJ_PACIENTES FOR EACH ROW '.
            'BEGIN :NEW.NOMBRE := UPPER(:NEW.NOMBRE); '.
            ':NEW.APELLIDO_P := UPPER(:NEW.APELLIDO_P); '.
            ':NEW.APELLIDO_M := UPPER(:NEW.APELLIDO_M); END;',
        );

        DB::unprepared(
            'CREATE OR REPLACE TRIGGER TRG_IMAJ_UPPER_USUARIOS '.
            'BEFORE INSERT OR UPDATE ON IMAJ_USUARIOS FOR EACH ROW '.
            'BEGIN :NEW.NOMBRE_USUARIO := UPPER(:NEW.NOMBRE_USUARIO); END;',
        );
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER TRG_IMAJ_UPPER_USUARIOS');
        DB::statement('DROP TRIGGER TRG_IMAJ_UPPER_PACIENTES');
    }
};
