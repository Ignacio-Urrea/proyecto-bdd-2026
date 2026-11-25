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
        DB::unprepared('CREATE TABLE IMAJ_PRESUPUESTOS (ID NUMBER, ESTADO NUMBER, ORDEN_ID NUMBER, CONSTRAINT PK_IMAJ_PRESUPUESTOS PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_PRESUPUESTOS_ORDENES FOREIGN KEY (ORDEN_ID) REFERENCES IMAJ_ORDENES (ID), CONSTRAINT UQ_IMAJ_PRESUPUESTOS UNIQUE (ORDEN_ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_PRESUPUESTOS START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_PRESUPUESTOS');
        DB::statement('DROP TABLE IMAJ_PRESUPUESTOS CASCADE CONSTRAINTS');
    }
};
