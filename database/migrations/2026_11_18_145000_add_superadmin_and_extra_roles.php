<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private const NEW_ROLES = [
        'SUPERADMIN',
        'CLINICA_ADMIN',
        'SUCURSAL_ADMIN',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Insertar los nuevos roles si no existen
        foreach (self::NEW_ROLES as $roleName) {
            $exists = DB::table('IMAJ_ROLES')
                ->whereRaw('UPPER(NOMBRE_ROL) = ?', [$roleName])
                ->exists();

            if (! $exists) {
                $maxId = (int) (DB::table('IMAJ_ROLES')->max('ID') ?? 0);
                DB::table('IMAJ_ROLES')->insert([
                    'ID' => $maxId + 1,
                    'NOMBRE_ROL' => $roleName,
                ]);
            }
        }

        // 2. Obtener el ID del rol SUPERADMIN
        $superadminRoleId = DB::table('IMAJ_ROLES')
            ->whereRaw('UPPER(NOMBRE_ROL) = ?', ['SUPERADMIN'])
            ->value('ID');

        // 3. Crear o actualizar el usuario superadmin con contraseña superadmin
        $userExists = DB::table('IMAJ_USUARIOS')
            ->whereRaw('UPPER(NOMBRE_USUARIO) = ?', ['SUPERADMIN'])
            ->exists();

        if (! $userExists) {
            $maxUserId = (int) (DB::table('IMAJ_USUARIOS')->max('ID') ?? 0);
            DB::table('IMAJ_USUARIOS')->insert([
                'ID' => $maxUserId + 1,
                'NOMBRE_USUARIO' => 'superadmin',
                'PASSWORD_HASH' => Hash::make('superadmin'),
                'ROL_ID' => $superadminRoleId,
            ]);
        } else {
            DB::table('IMAJ_USUARIOS')
                ->whereRaw('UPPER(NOMBRE_USUARIO) = ?', ['SUPERADMIN'])
                ->update([
                    'PASSWORD_HASH' => Hash::make('superadmin'),
                    'ROL_ID' => $superadminRoleId,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('IMAJ_USUARIOS')
            ->whereRaw('UPPER(NOMBRE_USUARIO) = ?', ['SUPERADMIN'])
            ->delete();

        foreach (self::NEW_ROLES as $roleName) {
            DB::table('IMAJ_ROLES')
                ->whereRaw('UPPER(NOMBRE_ROL) = ?', [$roleName])
                ->delete();
        }
    }
};
