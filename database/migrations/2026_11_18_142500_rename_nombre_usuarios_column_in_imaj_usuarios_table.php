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
        // 1. Renombrar NOMBRE_USUARIOS a NOMBRE_USUARIO si existe con ese nombre
        $column = DB::selectOne("
            SELECT COLUMN_NAME 
            FROM USER_TAB_COLUMNS 
            WHERE UPPER(TABLE_NAME) = 'IMAJ_USUARIOS' 
              AND UPPER(COLUMN_NAME) = 'NOMBRE_USUARIOS'
        ");

        if ($column) {
            $colName = $column->column_name ?? $column->COLUMN_NAME;
            DB::statement("ALTER TABLE IMAJ_USUARIOS RENAME COLUMN \"{$colName}\" TO NOMBRE_USUARIO");
        }

        // 2. Asegurar que exista la columna PASSWORD_HASH necesaria para la autenticación
        $passwordHashColumn = DB::selectOne("
            SELECT COLUMN_NAME 
            FROM USER_TAB_COLUMNS 
            WHERE UPPER(TABLE_NAME) = 'IMAJ_USUARIOS' 
              AND UPPER(COLUMN_NAME) = 'PASSWORD_HASH'
        ");

        if (! $passwordHashColumn) {
            DB::statement('ALTER TABLE IMAJ_USUARIOS ADD (PASSWORD_HASH VARCHAR2(255))');
        }

        // 3. Insertar los nuevos roles si no existen
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

        // 4. Obtener el ID del rol SUPERADMIN
        $superadminRoleId = DB::table('IMAJ_ROLES')
            ->whereRaw('UPPER(NOMBRE_ROL) = ?', ['SUPERADMIN'])
            ->value('ID');

        // 5. Crear o actualizar el usuario superadmin con contraseña superadmin
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

        $column = DB::selectOne("
            SELECT COLUMN_NAME 
            FROM USER_TAB_COLUMNS 
            WHERE UPPER(TABLE_NAME) = 'IMAJ_USUARIOS' 
              AND UPPER(COLUMN_NAME) = 'NOMBRE_USUARIO'
        ");

        if ($column) {
            DB::statement('ALTER TABLE IMAJ_USUARIOS RENAME COLUMN NOMBRE_USUARIO TO NOMBRE_USUARIOS');
        }
    }
};
