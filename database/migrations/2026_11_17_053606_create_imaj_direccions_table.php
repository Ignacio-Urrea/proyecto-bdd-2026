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
        DB::unprepared('CREATE TABLE IMAJ_DIRECCIONES (ID NUMBER, CIUDAD_ID NUMBER, CALLE VARCHAR2(22), CONSTRAINT PK_IMAJ_DIRECCIONES PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_DIRECCIONES_CIUDADES FOREIGN KEY (CIUDAD_ID) REFERENCES IMAJ_CIUDADES (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_DIRECCIONES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_DIRECCIONES');
        DB::statement('DROP TABLE IMAJ_DIRECCIONES CASCADE CONSTRAINTS');
    }
};
