<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\OracleCrudService;

class UsuarioController extends CrudController
{
    protected string $modelClass = Usuario::class;

    protected string $title = 'Usuarios';

    protected string $routeName = 'usuarios';

    protected array $fields = [
        ['name' => 'NOMBRE_USUARIO', 'label' => 'Nombre de usuario', 'type' => 'text'],
        ['name' => 'CONTRASEÑA', 'label' => 'Contraseña', 'type' => 'password', 'show_in_index' => false, 'show_in_view' => false, 'placeholder' => 'Deja vacío para conservarla'],
        ['name' => 'ROL_ID', 'label' => 'Rol', 'type' => 'select', 'options' => [self::class, 'roleOptions']],
    ];

    protected array $hashFields = ['CONTRASEÑA'];

    protected array $preserveOnEmpty = ['CONTRASEÑA'];

    public static function roleOptions(): array
    {
        return app(OracleCrudService::class)->options('IMAJ_ROLES', 'NOMBRE_ROL');
    }
}
