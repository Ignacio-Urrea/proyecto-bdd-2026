<?php

namespace App\Http\Controllers;

use App\Models\Prestacion;
use App\Services\OracleCrudService;

class PrestacionController extends CrudController
{
    protected string $modelClass = Prestacion::class;

    protected string $title = 'Prestaciones';

    protected string $routeName = 'prestaciones';

    protected array $fields = [
        ['name' => 'CLINICA_ID', 'label' => 'Clínica', 'type' => 'select', 'options' => [self::class, 'clinicOptions']],
        ['name' => 'NOMBRE', 'label' => 'Nombre', 'type' => 'text'],
        ['name' => 'PRECIO_BASE', 'label' => 'Precio base', 'type' => 'number', 'step' => '0.01'],
        ['name' => 'HABILITADA', 'label' => 'Habilitada', 'type' => 'select', 'options' => [1 => 'Sí', 0 => 'No']],
    ];

    public static function clinicOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_CLINICAS', 'NOMBRE_CLINICA');
    }
}
