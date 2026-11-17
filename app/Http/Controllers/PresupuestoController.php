<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Services\OracleCrudService;

class PresupuestoController extends CrudController
{
    protected string $modelClass = Presupuesto::class;

    protected string $title = 'Presupuestos';

    protected string $routeName = 'presupuestos';

    protected array $fields = [
        ['name' => 'ESTADO', 'label' => 'Estado', 'type' => 'number'],
        ['name' => 'ORDEN_ID', 'label' => 'Orden', 'type' => 'select', 'options' => [self::class, 'orderOptions']],
    ];

    protected array $rules = [
        'ESTADO' => ['required', 'integer'],
        'ORDEN_ID' => ['required', 'integer'],
    ];

    public static function orderOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_ORDENES', 'ID');
    }
}
