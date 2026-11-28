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
        $assignableRoles = array_map(
            static fn (string $role): string => strtoupper(trim($role)),
            config('imaj.assignable_user_roles', []),
        );

        return collect(app(OracleCrudService::class)->rows('IMAJ_ROLES'))
            ->filter(static fn (object $role): bool => in_array(
                strtoupper(trim((string) $role->NOMBRE_ROL)),
                $assignableRoles,
                true,
            ))
            ->mapWithKeys(static fn (object $role): array => [$role->ID => $role->NOMBRE_ROL])
            ->all();
    }
}
