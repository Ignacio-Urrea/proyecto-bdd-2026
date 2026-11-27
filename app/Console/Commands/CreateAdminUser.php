<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Crea el primer usuario administrador de IMAJ';

    public function handle(): int
    {
        $username = trim((string) $this->ask('Nombre de usuario (máximo 16 caracteres)'));

        if ($username === '' || mb_strlen($username) > 16) {
            $this->components->error('El nombre de usuario debe tener entre 1 y 16 caracteres.');

            return self::FAILURE;
        }

        if (Usuario::query()->where('NOMBRE_USUARIO', $username)->exists()) {
            $this->components->error('Ese nombre de usuario ya está registrado.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $confirmation = (string) $this->secret('Confirma la contraseña');

        if (mb_strlen($password) < 12 || ! hash_equals($password, $confirmation)) {
            $this->components->error('La contraseña debe tener 12 caracteres como mínimo y ambas entradas deben coincidir.');

            return self::FAILURE;
        }

        $role = Rol::query()->where('NOMBRE_ROL', 'ADMINISTRADOR')->firstOrFail();

        Usuario::query()->create([
            'NOMBRE_USUARIO' => $username,
            'CONTRASEÑA' => $password,
            'ROL_ID' => $role->ID,
        ]);

        $this->components->info("Administrador {$username} creado correctamente.");

        return self::SUCCESS;
    }
}
