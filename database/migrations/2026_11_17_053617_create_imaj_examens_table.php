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
        DB::unprepared('CREATE TABLE IMAJ_EXAMENES (ID NUMBER, ORDEN_ID NUMBER, URGENCIA NUMBER, FECHA_SOLICITUD DATE, ESTADO NUMBER, CONSTRAINT PK_IMAJ_EXAMENES PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_EXAMENES_ORDENES FOREIGN KEY (ORDEN_ID) REFERENCES IMAJ_ORDENES (ID), CONSTRAINT UQ_IMAJ_EXAMENES UNIQUE (ORDEN_ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_EXAMENES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_EXAMENES');
        DB::statement('DROP TABLE IMAJ_EXAMENES CASCADE CONSTRAINTS');
    }
};
