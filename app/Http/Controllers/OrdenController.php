<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Services\OracleCrudService;

class OrdenController extends CrudController
{
    protected string $modelClass = Orden::class;

    protected string $title = 'Órdenes';

    protected string $routeName = 'ordenes';

    protected array $fields = [
        ['name' => 'PACIENTE_ID', 'label' => 'Paciente', 'type' => 'select', 'options' => [self::class, 'patientOptions']],
        ['name' => 'SUCURSAL_ID', 'label' => 'Sucursal', 'type' => 'select', 'options' => [self::class, 'branchOptions']],
        ['name' => 'EJECUTADA', 'label' => 'Ejecutada', 'type' => 'select', 'options' => [1 => 'Sí', 0 => 'No']],
        ['name' => 'FECHA_CITA', 'label' => 'Fecha de cita', 'type' => 'date'],
        ['name' => 'HORA_CITA', 'label' => 'Hora de cita', 'type' => 'number'],
        ['name' => 'ESTADO', 'label' => 'Estado', 'type' => 'number'],
    ];

    protected array $rules = [
        'PACIENTE_ID' => ['required', 'integer'],
        'SUCURSAL_ID' => ['required', 'integer'],
        'EJECUTADA' => ['required', 'integer', 'in:0,1'],
        'FECHA_CITA' => ['required', 'date'],
        'HORA_CITA' => ['required', 'integer'],
        'ESTADO' => ['required', 'integer'],
    ];

    public static function patientOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_PACIENTES', 'RUT');
    }

    public static function branchOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_SUCURSALES', 'CORREO_SUCURSAL');
    }
}
