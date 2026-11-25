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
        DB::unprepared('CREATE TABLE IMAJ_PRESTACIONES (ID NUMBER, NOMBRE VARCHAR2(32), PRECIO_BASE NUMBER, HABILITADA NUMBER(1), CONSTRAINT PK_IMAJ_PRESTACIONES PRIMARY KEY (ID), CONSTRAINT CK_PREST_HABILITADA CHECK (HABILITADA IN (0, 1)))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_PRESTACIONES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_PRESTACIONES');
        DB::statement('DROP TABLE IMAJ_PRESTACIONES CASCADE CONSTRAINTS');
    }
};
