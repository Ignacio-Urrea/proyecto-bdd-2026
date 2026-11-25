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
        DB::unprepared('CREATE TABLE IMAJ_IMAGENES (ID NUMBER, TAMANIO NUMBER, NOMBRE VARCHAR2(64), TIPO VARCHAR2(16), RUTA VARCHAR2(64), EXAMEN_ID NUMBER, INFORME_ID NUMBER, CONSTRAINT PK_IMAJ_IMAGENES PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_IMAGENES_EXAMENES FOREIGN KEY (EXAMEN_ID) REFERENCES IMAJ_EXAMENES (ID), CONSTRAINT FK_IMAJ_IMAGENES_INFORMES FOREIGN KEY (INFORME_ID) REFERENCES IMAJ_INFORMES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_IMAGENES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_IMAGENES');
        DB::statement('DROP TABLE IMAJ_IMAGENES CASCADE CONSTRAINTS');
    }
};
