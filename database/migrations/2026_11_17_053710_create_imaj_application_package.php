<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLES = [
        'IMAJ_ROLES' => ['ROLES', 'SEQ_IMAJ_ROLES'],
        'IMAJ_REGIONES' => ['REGIONES', 'SEQ_IMAJ_REGIONES'],
        'IMAJ_PRESTACIONES' => ['PRESTACIONES', 'SEQ_IMAJ_PRESTACIONES'],
        'IMAJ_USUARIOS' => ['USUARIOS', 'SEQ_IMAJ_USUARIOS'],
        'IMAJ_CIUDADES' => ['CIUDADES', 'SEQ_IMAJ_CIUDADES'],
        'IMAJ_DIRECCIONES' => ['DIRECCIONES', 'SEQ_IMAJ_DIRECCIONES'],
        'IMAJ_CLINICAS' => ['CLINICAS', 'SEQ_IMAJ_CLINICAS'],
        'IMAJ_SUCURSALES' => ['SUCURSALES', 'SEQ_IMAJ_SUCURSALES'],
        'IMAJ_PACIENTES' => ['PACIENTES', 'SEQ_IMAJ_PACIENTES'],
        'IMAJ_ORDENES' => ['ORDENES', 'SEQ_IMAJ_ORDENES'],
        'IMAJ_EXAMENES' => ['EXAMENES', 'SEQ_IMAJ_EXAMENES'],
        'IMAJ_PRESUPUESTOS' => ['PRESUPUESTOS', 'SEQ_IMAJ_PRESUPUESTOS'],
        'IMAJ_PAGOS' => ['PAGOS', 'SEQ_IMAJ_PAGOS'],
        'IMAJ_DETALLE_PRESTACIONES' => ['DETALLE_PRESTACIONES', 'SEQ_IMAJ_DETALLE_PRESTACIONES'],
        'IMAJ_INFORMES' => ['INFORMES', 'SEQ_IMAJ_INFORMES'],
        'IMAJ_IMAGENES' => ['IMAGENES', 'SEQ_IMAJ_IMAGENES'],
    ];

    public function up(): void
    {
        DB::unprepared(
            'CREATE TABLE IMAJ_AUDITORIA (ID NUMBER PRIMARY KEY, TABLA VARCHAR2(30) NOT NULL, '.
            'OPERACION VARCHAR2(10) NOT NULL, REGISTRO_ID NUMBER NOT NULL, USUARIO_BD VARCHAR2(128), '.
            'FECHA_EVENTO TIMESTAMP WITH TIME ZONE NOT NULL)',
        );
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_AUDITORIA START WITH 1 INCREMENT BY 1 NOCACHE');

        $packageFile = database_path('oracle/IMAJ_APP_PKG.sql');

        if (! is_file($packageFile)) {
            throw new RuntimeException('No se encontró el archivo database/oracle/IMAJ_APP_PKG.sql.');
        }

        $statements = preg_split('/^\s*\/\s*$/m', file_get_contents($packageFile)) ?: [];

        foreach ($statements as $statement) {
            if (trim($statement) !== '') {
                DB::unprepared(trim($statement));
            }
        }

        foreach (self::TABLES as $table => [$suffix, $sequence]) {
            DB::unprepared(
                "CREATE OR REPLACE TRIGGER TRG_IMAJ_BI_{$suffix} ".
                "BEFORE INSERT ON {$table} FOR EACH ROW ".
                "BEGIN IF :NEW.ID IS NULL THEN :NEW.ID := {$sequence}.NEXTVAL; END IF; END;",
            );

            DB::unprepared(
                "CREATE OR REPLACE TRIGGER TRG_IMAJ_AUD_{$suffix} ".
                "AFTER INSERT OR UPDATE OR DELETE ON {$table} FOR EACH ROW ".
                'DECLARE V_ID NUMBER; V_OPERATION VARCHAR2(10); BEGIN '.
                "IF INSERTING THEN V_ID := :NEW.ID; V_OPERATION := 'INSERT'; ".
                "ELSIF UPDATING THEN V_ID := :NEW.ID; V_OPERATION := 'UPDATE'; ".
                "ELSE V_ID := :OLD.ID; V_OPERATION := 'DELETE'; END IF; ".
                'INSERT INTO IMAJ_AUDITORIA (ID, TABLA, OPERACION, REGISTRO_ID, USUARIO_BD, FECHA_EVENTO) '.
                "VALUES (SEQ_IMAJ_AUDITORIA.NEXTVAL, '{$table}', V_OPERATION, V_ID, ".
                "COALESCE(SYS_CONTEXT('USERENV', 'CLIENT_IDENTIFIER'), SYS_CONTEXT('USERENV', 'SESSION_USER')), ".
                'SYSTIMESTAMP); END;',
            );
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as [, $suffix]) {
            DB::statement("DROP TRIGGER TRG_IMAJ_AUD_{$suffix}");
            DB::statement("DROP TRIGGER TRG_IMAJ_BI_{$suffix}");
        }

        DB::statement('DROP PACKAGE IMAJ_APP_PKG');
        DB::statement('DROP SEQUENCE SEQ_IMAJ_AUDITORIA');
        DB::statement('DROP TABLE IMAJ_AUDITORIA');
    }
};
