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
        DB::unprepared('CREATE TABLE IMAJ_INFORMES (ID NUMBER, CUERPO VARCHAR2(1028), ADENDUM NUMBER(1), TITULO VARCHAR2(32), RUTA VARCHAR2(64), EXAMEN_ID NUMBER, CONSTRAINT PK_IMAJ_INFORMES PRIMARY KEY (ID), CONSTRAINT CK_INF_ADENDUM CHECK (ADENDUM IN (0, 1)), CONSTRAINT FK_INFORMES_EXAMENES FOREIGN KEY (EXAMEN_ID) REFERENCES IMAJ_EXAMENES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_INFORMES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_INFORMES');
        DB::statement('DROP TABLE IMAJ_INFORMES CASCADE CONSTRAINTS');
    }
};
