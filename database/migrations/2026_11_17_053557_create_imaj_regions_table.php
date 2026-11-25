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
        DB::unprepared('CREATE TABLE IMAJ_REGIONES (ID NUMBER, NOMBRE_REGION VARCHAR2(22), CONSTRAINT PK_IMAJ_REGIONES PRIMARY KEY (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_REGIONES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_REGIONES');
        DB::statement('DROP TABLE IMAJ_REGIONES CASCADE CONSTRAINTS');
    }
};
