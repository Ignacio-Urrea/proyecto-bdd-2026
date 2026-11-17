<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use App\Services\OracleCrudService;

class SucursalController extends CrudController
{
    protected string $modelClass = Sucursal::class;

    protected string $title = 'Sucursales';

    protected string $routeName = 'sucursales';

    protected array $fields = [
        ['name' => 'ADMINISTRADOR_ID', 'label' => 'Administrador', 'type' => 'select', 'options' => [self::class, 'userOptions']],
        ['name' => 'CLINICA_ID', 'label' => 'Clínica', 'type' => 'select', 'options' => [self::class, 'clinicOptions']],
        ['name' => 'CORREO_SUCURSAL', 'label' => 'Correo', 'type' => 'email'],
        ['name' => 'DIRECCION_ID', 'label' => 'Dirección', 'type' => 'select', 'options' => [self::class, 'addressOptions']],
    ];

    public static function userOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_USUARIOS', 'NOMBRE_USUARIO');
    }

    public static function clinicOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_CLINICAS', 'NOMBRE_CLINICA');
    }

    public static function addressOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_DIRECCIONES', 'CALLE');
    }
}
