<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Services\OracleCrudService;

class PagoController extends CrudController
{
    protected string $modelClass = Pago::class;

    protected string $title = 'Pagos';

    protected string $routeName = 'pagos';

    protected array $fields = [
        ['name' => 'PRESUPUESTO_ID', 'label' => 'Presupuesto', 'type' => 'select', 'options' => [self::class, 'budgetOptions']],
        ['name' => 'MONTO', 'label' => 'Monto', 'type' => 'number', 'step' => '0.01'],
        ['name' => 'METODO_PAGO', 'label' => 'Método de pago', 'type' => 'text'],
    ];

    protected array $rules = [
        'PRESUPUESTO_ID' => ['required', 'integer'],
        'MONTO' => ['required', 'numeric', 'min:0'],
        'METODO_PAGO' => ['required', 'string', 'max:32'],
    ];

    public static function budgetOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_PRESUPUESTOS', 'ID');
    }
}
