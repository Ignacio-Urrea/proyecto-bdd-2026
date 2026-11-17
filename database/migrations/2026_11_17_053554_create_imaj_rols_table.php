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
        DB::unprepared('CREATE TABLE IMAJ_ROLES (ID NUMBER, NOMBRE_ROL VARCHAR2(16), CONSTRAINT PK_IMAJ_ROLES PRIMARY KEY (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_ROLES START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_ROLES');
        DB::statement('DROP TABLE IMAJ_ROLES CASCADE CONSTRAINTS');
    }
};
