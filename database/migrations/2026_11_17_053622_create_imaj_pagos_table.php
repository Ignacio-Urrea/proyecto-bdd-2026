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
        DB::unprepared('CREATE TABLE IMAJ_PAGOS (ID NUMBER, PRESUPUESTO_ID NUMBER, MONTO NUMBER, METODO_PAGO VARCHAR2(32), CONSTRAINT PK_IMAJ_PAGOS PRIMARY KEY (ID), CONSTRAINT FK_IMAJ_PAGOS_PRESUPUESTOS FOREIGN KEY (PRESUPUESTO_ID) REFERENCES IMAJ_PRESUPUESTOS (ID))');
        DB::unprepared('CREATE SEQUENCE SEQ_IMAJ_PAGOS START WITH 1 INCREMENT BY 1 NOCACHE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SEQUENCE SEQ_IMAJ_PAGOS');
        DB::statement('DROP TABLE IMAJ_PAGOS CASCADE CONSTRAINTS');
    }
};
