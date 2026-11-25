<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private const ROLES = [
        'ADMINISTRADOR',
        'RECEPCIONISTA',
        'TECNICO',
        'RADIOLOGO',
        'FINANZAS',
    ];

    private const SEQUENCES = [
        'IMAJ_ROLES' => 'SEQ_IMAJ_ROLES',
        'IMAJ_REGIONES' => 'SEQ_IMAJ_REGIONES',
        'IMAJ_PRESTACIONES' => 'SEQ_IMAJ_PRESTACIONES',
        'IMAJ_USUARIOS' => 'SEQ_IMAJ_USUARIOS',
        'IMAJ_CIUDADES' => 'SEQ_IMAJ_CIUDADES',
        'IMAJ_DIRECCIONES' => 'SEQ_IMAJ_DIRECCIONES',
        'IMAJ_CLINICAS' => 'SEQ_IMAJ_CLINICAS',
        'IMAJ_SUCURSALES' => 'SEQ_IMAJ_SUCURSALES',
        'IMAJ_PACIENTES' => 'SEQ_IMAJ_PACIENTES',
        'IMAJ_ORDENES' => 'SEQ_IMAJ_ORDENES',
        'IMAJ_EXAMENES' => 'SEQ_IMAJ_EXAMENES',
        'IMAJ_PRESUPUESTOS' => 'SEQ_IMAJ_PRESUPUESTOS',
        'IMAJ_PAGOS' => 'SEQ_IMAJ_PAGOS',
        'IMAJ_DETALLE_PRESTACIONES' => 'SEQ_IMAJ_DETALLE_PRESTACIONES',
        'IMAJ_INFORMES' => 'SEQ_IMAJ_INFORMES',
        'IMAJ_IMAGENES' => 'SEQ_IMAJ_IMAGENES',
    ];

    public function up(): void
    {
        DB::statement('ALTER TABLE IMAJ_USUARIOS ADD (PASSWORD_HASH VARCHAR2(255))');
        DB::statement('ALTER TABLE IMAJ_USUARIOS ADD CONSTRAINT UQ_IMAJ_USUARIOS_NOMBRE UNIQUE (NOMBRE_USUARIO)');

        foreach (self::SEQUENCES as $table => $sequence) {
            $this->synchronizeSequence($table, $sequence);
        }

        foreach (self::ROLES as $role) {
            $exists = DB::table('IMAJ_ROLES')
                ->whereRaw('UPPER(NOMBRE_ROL) = ?', [$role])
                ->exists();

            if (! $exists) {
                DB::statement(
                    'INSERT INTO IMAJ_ROLES (ID, NOMBRE_ROL) SELECT SEQ_IMAJ_ROLES.NEXTVAL, ? FROM DUAL',
                    [$role],
                );
            }
        }

        foreach (DB::table('IMAJ_USUARIOS')->select('ID', 'CONTRASEÑA')->whereNotNull('CONTRASEÑA')->get() as $user) {
            DB::table('IMAJ_USUARIOS')
                ->where('ID', $user->ID)
                ->update(['PASSWORD_HASH' => Hash::make((string) $user->{'CONTRASEÑA'})]);
        }
    }

    private function synchronizeSequence(string $table, string $sequence): void
    {
        $maximumId = (int) (DB::table($table)->max('ID') ?? 0);
        $row = DB::selectOne("SELECT {$sequence}.NEXTVAL AS ID FROM DUAL");
        $nextId = (int) ($row->id ?? $row->ID);

        if ($nextId <= $maximumId) {
            $increment = $maximumId + 1 - $nextId;
            DB::statement("ALTER SEQUENCE {$sequence} INCREMENT BY {$increment}");
            DB::selectOne("SELECT {$sequence}.NEXTVAL AS ID FROM DUAL");
            DB::statement("ALTER SEQUENCE {$sequence} INCREMENT BY 1");
        }
    }

    public function down(): void
    {
        if (DB::table('IMAJ_USUARIOS')->whereNotNull('PASSWORD_HASH')->exists()) {
            throw new RuntimeException('No se puede revertir la autenticación mientras existan contraseñas migradas.');
        }

        DB::statement('ALTER TABLE IMAJ_USUARIOS DROP CONSTRAINT UQ_IMAJ_USUARIOS_NOMBRE');

        foreach (self::ROLES as $role) {
            DB::table('IMAJ_ROLES')
                ->where('NOMBRE_ROL', $role)
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('IMAJ_USUARIOS')
                        ->whereColumn('IMAJ_USUARIOS.ROL_ID', 'IMAJ_ROLES.ID');
                })
                ->delete();
        }

        DB::statement('ALTER TABLE IMAJ_USUARIOS DROP COLUMN PASSWORD_HASH');
    }
};
