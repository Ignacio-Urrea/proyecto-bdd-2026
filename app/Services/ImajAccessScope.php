<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ImajAccessScope
{
    private array $cache = [];

    public function restricted(): bool
    {
        return in_array($this->role(), ['CLINICA_ADMIN', 'SUCURSAL_ADMIN', 'RADIOLOGO'], true);
    }

    public function ids(string $table): ?array
    {
        $table = strtoupper($table);

        if (! $this->restricted()) {
            return null;
        }

        if (array_key_exists($table, $this->cache)) {
            return $this->cache[$table];
        }

        $ids = match ($table) {
            'IMAJ_ROLES' => [],
            'IMAJ_USUARIOS' => $this->userIds(),
            'IMAJ_CLINICAS' => $this->role() === 'RADIOLOGO' ? [] : $this->clinicIds(),
            'IMAJ_SUCURSALES' => $this->role() === 'RADIOLOGO' ? [] : $this->branchIds(),
            'IMAJ_PACIENTES' => $this->role() === 'RADIOLOGO' ? [] : $this->patientIds(),
            'IMAJ_ORDENES' => $this->orderIds(),
            'IMAJ_EXAMENES' => $this->examIds(),
            'IMAJ_INFORMES' => $this->reportIds(),
            'IMAJ_PRESUPUESTOS' => $this->role() === 'RADIOLOGO' ? [] : $this->budgetIds(),
            'IMAJ_PAGOS', 'IMAJ_DETALLE_PRESTACIONES' => $this->role() === 'RADIOLOGO'
                ? []
                : $this->idsWhereIn($table, 'PRESUPUESTO_ID', $this->budgetIds()),
            'IMAJ_IMAGENES' => $this->imageIds(),
            'IMAJ_DIRECCIONES' => $this->role() === 'RADIOLOGO' ? [] : $this->addressIds(),
            'IMAJ_CIUDADES' => $this->role() === 'RADIOLOGO' ? [] : $this->cityIds(),
            'IMAJ_REGIONES' => $this->role() === 'RADIOLOGO' ? [] : $this->regionIds(),
            'IMAJ_PRESTACIONES' => $this->role() === 'RADIOLOGO' ? [] : $this->allIds($table),
            default => [],
        };

        return $this->cache[$table] = $ids;
    }

    public function allows(string $table, int $id): bool
    {
        $ids = $this->ids($table);

        return $ids === null || in_array($id, $ids, true);
    }

    public function assertCanAccess(string $table, int $id): void
    {
        abort_unless($this->allows($table, $id), 404);
    }

    public function assertCanReference(string $table, int $id): void
    {
        $this->assertCanAccess($table, $id);
    }

    public function assertCanCreate(string $table, array $data): void
    {
        $this->assertCanWriteData($table, $data, false);
    }

    public function assertCanUpdate(string $table, int $id, array $data): void
    {
        $this->assertCanAccess($table, $id);
        $this->assertCanWriteData($table, $data, true);
    }

    public function assertCanDelete(string $table, int $id): void
    {
        $this->assertCanAccess($table, $id);

        if ($this->restricted() && in_array(strtoupper($table), ['IMAJ_USUARIOS', 'IMAJ_ROLES', 'IMAJ_PRESTACIONES'], true)) {
            $this->deny();
        }

        if ($this->role() === 'RADIOLOGO') {
            $this->deny();
        }
    }

    private function assertCanWriteData(string $table, array $data, bool $updating): void
    {
        $table = strtoupper($table);

        if (! $this->restricted()) {
            return;
        }

        $role = $this->role();

        if ($role === 'RADIOLOGO') {
            if ($table === 'IMAJ_INFORMES' && isset($data['EXAMEN_ID'])) {
                $this->assertCanReference('IMAJ_EXAMENES', (int) $data['EXAMEN_ID']);

                return;
            }

            if ($table === 'IMAJ_EXAMENES' && $updating
                && (int) ($data['RADIOLOGO_ID'] ?? $this->userId()) === $this->userId()) {
                return;
            }

            $this->deny();
        }

        switch ($table) {
            case 'IMAJ_CLINICAS':
                if ($role === 'CLINICA_ADMIN' && $updating) {
                    if (isset($data['ADMINISTRADOR_ID']) && (int) $data['ADMINISTRADOR_ID'] !== $this->userId()) {
                        $this->deny();
                    }

                    return;
                }
                $this->deny();
                break;

            case 'IMAJ_SUCURSALES':
                if ($role === 'CLINICA_ADMIN' && isset($data['CLINICA_ID'])
                    && $this->allows('IMAJ_CLINICAS', (int) $data['CLINICA_ID'])) {
                    if (isset($data['ADMINISTRADOR_ID'])) {
                        $this->assertCanReference('IMAJ_USUARIOS', (int) $data['ADMINISTRADOR_ID']);
                        $this->assertRadiologistOrBranchAdmin((int) $data['ADMINISTRADOR_ID']);
                    }

                    return;
                }
                if ($role === 'SUCURSAL_ADMIN' && $updating) {
                    if (isset($data['CLINICA_ID'])) {
                        $this->assertCanReference('IMAJ_CLINICAS', (int) $data['CLINICA_ID']);
                    }
                    if (isset($data['ADMINISTRADOR_ID']) && (int) $data['ADMINISTRADOR_ID'] !== $this->userId()) {
                        $this->deny();
                    }

                    return;
                }
                $this->deny();
                break;

            case 'IMAJ_DIRECCIONES':
                $this->assertCanReference('IMAJ_CIUDADES', (int) ($data['CIUDAD_ID'] ?? 0));

                return;

            case 'IMAJ_PACIENTES':
                if (! in_array($role, ['CLINICA_ADMIN', 'SUCURSAL_ADMIN'], true)) {
                    $this->deny();
                }
                $this->assertCanReference('IMAJ_SUCURSALES', (int) ($data['SUCURSAL_ID'] ?? 0));

                return;

            case 'IMAJ_ORDENES':
                $this->assertCanReference('IMAJ_PACIENTES', (int) ($data['PACIENTE_ID'] ?? 0));
                $this->assertCanReference('IMAJ_SUCURSALES', (int) ($data['SUCURSAL_ID'] ?? 0));

                return;

            case 'IMAJ_EXAMENES':
                $this->assertCanReference('IMAJ_ORDENES', (int) ($data['ORDEN_ID'] ?? 0));
                if (isset($data['RADIOLOGO_ID']) && $data['RADIOLOGO_ID'] !== '') {
                    $this->assertRadiologist((int) $data['RADIOLOGO_ID']);
                }

                return;

            case 'IMAJ_PRESUPUESTOS':
                $this->assertCanReference('IMAJ_ORDENES', (int) ($data['ORDEN_ID'] ?? 0));

                return;

            case 'IMAJ_PAGOS':
            case 'IMAJ_DETALLE_PRESTACIONES':
                $this->assertCanReference('IMAJ_PRESUPUESTOS', (int) ($data['PRESUPUESTO_ID'] ?? 0));

                return;

            case 'IMAJ_INFORMES':
                $this->assertCanReference('IMAJ_EXAMENES', (int) ($data['EXAMEN_ID'] ?? 0));

                return;

            case 'IMAJ_IMAGENES':
                if (! empty($data['EXAMEN_ID'])) {
                    $this->assertCanReference('IMAJ_EXAMENES', (int) $data['EXAMEN_ID']);
                }
                if (! empty($data['INFORME_ID'])) {
                    $this->assertCanReference('IMAJ_INFORMES', (int) $data['INFORME_ID']);
                }
                if (empty($data['EXAMEN_ID']) && empty($data['INFORME_ID'])) {
                    $this->deny();
                }

                return;

            default:
                $this->deny();
        }
    }

    private function userIds(): array
    {
        if ($this->role() === 'RADIOLOGO') {
            return [$this->userId()];
        }

        $ids = [$this->userId()];
        $managedUserIds = DB::table('IMAJ_SUCURSALES')
            ->whereIn('ID', $this->branchIds())
            ->pluck('ADMINISTRADOR_ID')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($ids, $managedUserIds)));
    }

    private function clinicIds(): array
    {
        if ($this->role() === 'CLINICA_ADMIN') {
            return $this->idsWhere('IMAJ_CLINICAS', 'ADMINISTRADOR_ID', $this->userId());
        }

        return $this->valuesWhereIn('IMAJ_SUCURSALES', 'CLINICA_ID', 'ID', $this->branchIds());
    }

    private function branchIds(): array
    {
        if ($this->role() === 'SUCURSAL_ADMIN') {
            return $this->idsWhere('IMAJ_SUCURSALES', 'ADMINISTRADOR_ID', $this->userId());
        }

        if ($this->role() === 'CLINICA_ADMIN') {
            return $this->idsWhereIn('IMAJ_SUCURSALES', 'CLINICA_ID', $this->clinicIds());
        }

        return $this->valuesWhereIn('IMAJ_ORDENES', 'SUCURSAL_ID', 'ID', $this->orderIds());
    }

    private function patientIds(): array
    {
        if ($this->role() === 'RADIOLOGO') {
            return [];
        }

        return $this->idsWhereIn('IMAJ_PACIENTES', 'SUCURSAL_ID', $this->branchIds());
    }

    private function orderIds(): array
    {
        if ($this->role() === 'RADIOLOGO') {
            return $this->idsWhereIn('IMAJ_EXAMENES', 'ORDEN_ID', $this->examIds());
        }

        return $this->idsWhereIn('IMAJ_ORDENES', 'SUCURSAL_ID', $this->branchIds());
    }

    private function examIds(): array
    {
        if ($this->role() === 'RADIOLOGO') {
            return $this->idsWhere('IMAJ_EXAMENES', 'RADIOLOGO_ID', $this->userId());
        }

        return $this->idsWhereIn('IMAJ_EXAMENES', 'ORDEN_ID', $this->orderIds());
    }

    private function reportIds(): array
    {
        return $this->idsWhereIn('IMAJ_INFORMES', 'EXAMEN_ID', $this->examIds());
    }

    private function budgetIds(): array
    {
        return $this->idsWhereIn('IMAJ_PRESUPUESTOS', 'ORDEN_ID', $this->orderIds());
    }

    private function imageIds(): array
    {
        return DB::table('IMAJ_IMAGENES')
            ->whereIn('EXAMEN_ID', $this->examIds())
            ->orWhereIn('INFORME_ID', $this->reportIds())
            ->pluck('ID')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function addressIds(): array
    {
        return array_values(array_unique(array_merge(
            $this->valuesWhereIn('IMAJ_CLINICAS', 'DIRECCION_ID', 'ID', $this->clinicIds()),
            $this->valuesWhereIn('IMAJ_SUCURSALES', 'DIRECCION_ID', 'ID', $this->branchIds()),
        )));
    }

    private function cityIds(): array
    {
        return $this->valuesWhereIn('IMAJ_DIRECCIONES', 'CIUDAD_ID', 'ID', $this->addressIds());
    }

    private function regionIds(): array
    {
        return $this->valuesWhereIn('IMAJ_CIUDADES', 'REGION_ID', 'ID', $this->cityIds());
    }

    private function allIds(string $table): array
    {
        return DB::table($table)->pluck('ID')->map(static fn ($id): int => (int) $id)->all();
    }

    private function idsWhere(string $table, string $column, int $value): array
    {
        return DB::table($table)
            ->where($column, $value)
            ->pluck('ID')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function idsWhereIn(string $table, string $column, array $values): array
    {
        if ($values === []) {
            return [];
        }

        return DB::table($table)
            ->whereIn($column, $values)
            ->pluck('ID')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function valuesWhereIn(string $table, string $valueColumn, string $filterColumn, array $values): array
    {
        if ($values === []) {
            return [];
        }

        return DB::table($table)
            ->whereIn($filterColumn, $values)
            ->pluck($valueColumn)
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function assertRadiologist(int $userId): void
    {
        $this->assertUserHasRole($userId, 'RADIOLOGO');
    }

    private function assertRadiologistOrBranchAdmin(int $userId): void
    {
        $this->assertUserHasRole($userId, 'SUCURSAL_ADMIN');
    }

    private function assertUserHasRole(int $userId, string $role): void
    {
        $hasRole = DB::table('IMAJ_USUARIOS as u')
            ->join('IMAJ_ROLES as r', 'r.ID', '=', 'u.ROL_ID')
            ->where('u.ID', $userId)
            ->whereRaw('UPPER(r.NOMBRE_ROL) = ?', [$role])
            ->exists();

        if (! $hasRole) {
            $this->deny();
        }
    }

    private function role(): string
    {
        return strtoupper((string) Auth::user()?->rol?->NOMBRE_ROL);
    }

    private function userId(): int
    {
        return (int) Auth::id();
    }

    private function deny(): never
    {
        throw new HttpException(403, 'No tienes permiso para operar sobre datos fuera de tu ámbito.');
    }
}
