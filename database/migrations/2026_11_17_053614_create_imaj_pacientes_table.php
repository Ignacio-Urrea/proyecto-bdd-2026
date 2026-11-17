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
        DB::unprepared('CREATE TABLE IMAJ_PACIENTES (ID NUMBER, CLINICA_ID NUMBER, FECHA_NACIMIENTO DATE, NOMBRE VARCHAR2(32), APELLIDO_P VARCHAR2(32), APELLIDO_M VARCHAR2(32), RUT VARCHAR2(32), TELEFONO NUMBER, CONSTRAINT PK_IMAJ_PACIENTES PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_PACIENTES_CLINICA FOREIGN KEY (CLINICA_ID) REFERENCES IMAJ_CLINICAS (ID), CONSTRAINT UQ_IMAJ_PACIENTES UNIQUE (RUT))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_PACIENTES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_PACIENTES');
        DB::statement('DROP TABLE IMAJ_PACIENTES CASCADE CONSTRAINTS');
    }
};
