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
        DB::unprepared('CREATE TABLE IMAJ_CIUDADES (ID NUMBER, REGION_ID NUMBER, NOMBRE_CIUDAD VARCHAR2(32), CONSTRAINT PK_IMAJ_CIUDADES PRIMARY KEY (ID), CONSTRAINT UQ_IMAJ_CIUDADES_NOMBRE UNIQUE (NOMBRE_CIUDAD), CONSTRAINT FK_IMAJ_CIUDADES_REGIONES FOREIGN KEY (REGION_ID) REFERENCES IMAJ_REGIONES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_CIUDADES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_CIUDADES');
        DB::statement('DROP TABLE IMAJ_CIUDADES CASCADE CONSTRAINTS');
    }
};
