<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('IMAJ_ROLES')
            ->whereRaw('UPPER(NOMBRE_ROL) = ?', ['SUPERADMIN'])
            ->exists();

        if (! $exists) {
            DB::statement(
                'INSERT INTO IMAJ_ROLES (ID, NOMBRE_ROL) SELECT SEQ_IMAJ_ROLES.NEXTVAL, ? FROM DUAL',
                ['SUPERADMIN'],
            );
        }
    }

    public function down(): void
    {
        DB::table('IMAJ_ROLES')
            ->whereRaw('UPPER(NOMBRE_ROL) = ?', ['SUPERADMIN'])
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('IMAJ_USUARIOS')
                    ->whereColumn('IMAJ_USUARIOS.ROL_ID', 'IMAJ_ROLES.ID');
            })
            ->delete();
    }
};
