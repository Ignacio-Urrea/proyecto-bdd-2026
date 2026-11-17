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
        DB::unprepared('CREATE TABLE IMAJ_USUARIOS (ID NUMBER, NOMBRE_USUARIO VARCHAR2(16), CONTRASEÑA VARCHAR2(32), ROL_ID NUMBER, CONSTRAINT PK_IMAJ_USUARIOS PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_USUARIOS_ROLES FOREIGN KEY (ROL_ID) REFERENCES IMAJ_ROLES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_USUARIOS START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_USUARIOS');
        DB::statement('DROP TABLE IMAJ_USUARIOS CASCADE CONSTRAINTS');
    }
};
