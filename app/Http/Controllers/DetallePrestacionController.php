<?php

namespace App\Http\Controllers;

use App\Models\DetallePrestacion;
use App\Services\OracleCrudService;

class DetallePrestacionController extends CrudController
{
    protected string $modelClass = DetallePrestacion::class;

    protected string $title = 'Detalle de prestaciones';

    protected string $routeName = 'detalle-prestaciones';

    protected array $fields = [
        ['name' => 'PRESUPUESTO_ID', 'label' => 'Presupuesto', 'type' => 'select', 'options' => [self::class, 'budgetOptions']],
        ['name' => 'PRESTACION_ID', 'label' => 'Prestación', 'type' => 'select', 'options' => [self::class, 'serviceOptions']],
        ['name' => 'PRECIO', 'label' => 'Precio', 'type' => 'number', 'step' => '0.01'],
    ];

    public static function budgetOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_PRESUPUESTOS', 'ID');
    }

    public static function serviceOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_PRESTACIONES', 'NOMBRE');
    }
}
