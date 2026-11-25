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
        DB::unprepared('CREATE TABLE IMAJ_CLINICAS (ID NUMBER, ADMINISTRADOR_ID NUMBER, DIRECCION_ID NUMBER, NOMBRE_CLINICA VARCHAR2(32), CORREO_CLINICA VARCHAR2(32), CONSTRAINT PK_IMAJ_CLINICAS PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_CLINICAS_DIRECCIONES FOREIGN KEY (DIRECCION_ID) REFERENCES IMAJ_DIRECCIONES (ID), CONSTRAINT FK_IMAJ_CLINICAS_USUARIOS FOREIGN KEY (ADMINISTRADOR_ID) REFERENCES IMAJ_USUARIOS (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_CLINICAS START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_CLINICAS');
        DB::statement('DROP TABLE IMAJ_CLINICAS CASCADE CONSTRAINTS');
    }
};
