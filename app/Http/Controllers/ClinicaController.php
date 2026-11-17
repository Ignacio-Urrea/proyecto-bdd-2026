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

    protected array $rules = [
        'ADMINISTRADOR_ID' => ['required', 'integer'],
        'DIRECCION_ID' => ['required', 'integer'],
        'NOMBRE_CLINICA' => ['required', 'string', 'max:32'],
        'CORREO_CLINICA' => ['required', 'email', 'max:32'],
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
