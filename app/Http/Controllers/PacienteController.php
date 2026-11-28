<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\OracleCrudService;

class PacienteController extends CrudController
{
    protected string $modelClass = Paciente::class;

    protected string $title = 'Pacientes';

    protected string $routeName = 'pacientes';

    protected array $fields = [
        ['name' => 'SUCURSAL_ID', 'label' => 'Sucursal', 'type' => 'select', 'options' => [self::class, 'branchOptions']],
        ['name' => 'FECHA_NACIMIENTO', 'label' => 'Fecha de nacimiento', 'type' => 'date'],
        ['name' => 'NOMBRE', 'label' => 'Nombre', 'type' => 'text'],
        ['name' => 'APELLIDO_P', 'label' => 'Apellido paterno', 'type' => 'text'],
        ['name' => 'APELLIDO_M', 'label' => 'Apellido materno', 'type' => 'text'],
        ['name' => 'RUT', 'label' => 'RUT', 'type' => 'text'],
        ['name' => 'TELEFONO', 'label' => 'Teléfono', 'type' => 'number'],
    ];

    public static function branchOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_SUCURSALES', 'CORREO_SUCURSAL');
    }
}
