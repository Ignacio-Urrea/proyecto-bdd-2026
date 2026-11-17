<?php

namespace App\Http\Controllers;

use App\Models\Rol;

class RolController extends CrudController
{
    protected string $modelClass = Rol::class;

    protected string $title = 'Roles';

    protected string $routeName = 'roles';

    protected array $fields = [
        ['name' => 'NOMBRE_ROL', 'label' => 'Nombre del rol', 'type' => 'text'],
    ];
}
