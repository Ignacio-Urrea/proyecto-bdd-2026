<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use App\Services\OracleCrudService;

class DireccionController extends CrudController
{
    protected string $modelClass = Direccion::class;

    protected string $title = 'Direcciones';

    protected string $routeName = 'direcciones';

    protected array $fields = [
        ['name' => 'CIUDAD_ID', 'label' => 'Ciudad', 'type' => 'select', 'options' => [self::class, 'cityOptions']],
        ['name' => 'CALLE', 'label' => 'Calle', 'type' => 'text'],
    ];

    protected array $rules = [
        'CIUDAD_ID' => ['required', 'integer'],
        'CALLE' => ['required', 'string', 'max:22'],
    ];

    public static function cityOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_CIUDADES', 'NOMBRE_CIUDAD');
    }
}
