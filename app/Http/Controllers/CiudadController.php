<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Services\OracleCrudService;

class CiudadController extends CrudController
{
    protected string $modelClass = Ciudad::class;

    protected string $title = 'Ciudades';

    protected string $routeName = 'ciudades';

    protected array $fields = [
        ['name' => 'REGION_ID', 'label' => 'Región', 'type' => 'select', 'options' => [self::class, 'regionOptions']],
        ['name' => 'NOMBRE_CIUDAD', 'label' => 'Nombre de la ciudad', 'type' => 'text'],
    ];

    public static function regionOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_REGIONES', 'NOMBRE_REGION');
    }
}
