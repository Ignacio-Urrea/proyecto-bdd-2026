<?php

namespace App\Http\Controllers;

use App\Models\Region;

class RegionController extends CrudController
{
    protected string $modelClass = Region::class;

    protected string $title = 'Regiones';

    protected string $routeName = 'regiones';

    protected array $fields = [
        ['name' => 'NOMBRE_REGION', 'label' => 'Nombre de la región', 'type' => 'text'],
    ];
}
