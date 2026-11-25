<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('CREATE TABLE IMAJ_ORDENES (ID NUMBER, PACIENTE_ID NUMBER, SUCURSAL_ID NUMBER, EJECUTADA NUMBER(1), FECHA_CITA DATE, HORA_CITA NUMBER, ESTADO NUMBER, CONSTRAINT PK_IMAJ_ORDENES PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_ORDENES_PACIENTES FOREIGN KEY (PACIENTE_ID) REFERENCES IMAJ_PACIENTES (ID), CONSTRAINT CK_ORD_EJECUTADA CHECK (EJECUTADA IN (0, 1)), CONSTRAINT FK_IMAJ_ORDENES_SUCURSALES FOREIGN KEY (SUCURSAL_ID) REFERENCES IMAJ_SUCURSALES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_ORDENES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_ORDENES');
        DB::statement('DROP TABLE IMAJ_ORDENES CASCADE CONSTRAINTS');
    }
};
