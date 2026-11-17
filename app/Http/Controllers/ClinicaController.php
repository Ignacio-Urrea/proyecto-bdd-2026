<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Services\OracleCrudService;

class ClinicaController extends CrudController
{
    protected string $modelClass = Clinica::class;

    protected string $title = 'Clínicas';

    protected string $routeName = 'clinicas';

    protected array $fields = [
        ['name' => 'ADMINISTRADOR_ID', 'label' => 'Administrador', 'type' => 'select', 'options' => [self::class, 'userOptions']],
        ['name' => 'DIRECCION_ID', 'label' => 'Dirección', 'type' => 'select', 'options' => [self::class, 'addressOptions']],
        ['name' => 'NOMBRE_CLINICA', 'label' => 'Nombre', 'type' => 'text'],
        ['name' => 'CORREO_CLINICA', 'label' => 'Correo', 'type' => 'email'],
    ];

    public static function userOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_USUARIOS', 'NOMBRE_USUARIO');
    }

    public static function addressOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_DIRECCIONES', 'CALLE');
    }
}
