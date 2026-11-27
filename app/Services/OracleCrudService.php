<?php

namespace App\Services;

use App\Events\OracleOperationCompleted;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;
use PDOException;

class OracleCrudService
{
    private const TABLES = [
        'IMAJ_ROLES',
        'IMAJ_REGIONES',
        'IMAJ_PRESTACIONES',
        'IMAJ_USUARIOS',
        'IMAJ_CIUDADES',
        'IMAJ_DIRECCIONES',
        'IMAJ_CLINICAS',
        'IMAJ_SUCURSALES',
        'IMAJ_PACIENTES',
        'IMAJ_ORDENES',
        'IMAJ_EXAMENES',
        'IMAJ_PRESUPUESTOS',
        'IMAJ_PAGOS',
        'IMAJ_DETALLE_PRESTACIONES',
        'IMAJ_INFORMES',
        'IMAJ_IMAGENES',
    ];

    private const MANAGERS = [
        'CREATE_ORDER',
        'BUILD_BUDGET',
        'REGISTER_PAYMENT',
        'COMPLETE_EXAM',
        'PUBLISH_REPORT',
    ];

    private const REPORTS = [
        'REPORT_ORDERS',
        'REPORT_REVENUE',
        'REPORT_EXAMS',
    ];

    /**
     * @return array<int, object>
     */
    public function rows(string $table): array
    {
        $table = $this->assertTable($table);
        $rows = DB::connection()->executeProcedureWithCursor(
            'IMAJ_APP_PKG.LIST_ROWS',
            ['p_table' => $table],
            ':p_cursor',
        );

        return $this->hideCredentials($table, $this->normalizeColumnNames($rows));
    }

    public function find(string $table, int $id): ?object
    {
        $table = $this->assertTable($table);
        $rows = DB::connection()->executeProcedureWithCursor(
            'IMAJ_APP_PKG.FIND_ROW',
            ['p_table' => $table, 'p_id' => $id],
            ':p_cursor',
        );

        return $this->hideCredentials($table, $this->normalizeColumnNames($rows))[0] ?? null;
    }

    public function paginate(
        string $table,
        int $perPage = 20,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): LengthAwarePaginator
    {
        $rows = collect($this->rows($table));

        $dateColumn = match (strtoupper($table)) {
            'IMAJ_ORDENES' => 'FECHA_CITA',
            'IMAJ_EXAMENES' => 'FECHA_SOLICITUD',
            default => null,
        };

        if ($dateColumn !== null && ($dateFrom !== null || $dateTo !== null)) {
            $rows = $rows->filter(function (object $row) use ($dateColumn, $dateFrom, $dateTo): bool {
                $rawDate = $row->{$dateColumn} ?? '';
                $rawDate = $rawDate instanceof \DateTimeInterface
                    ? $rawDate->format('Y-m-d')
                    : trim((string) $rawDate);

                if (! preg_match('/^\d{4}-\d{2}-\d{2}/', $rawDate, $matches)) {
                    return false;
                }

                $date = $matches[0];

                return ($dateFrom === null || $date >= $dateFrom)
                    && ($dateTo === null || $date <= $dateTo);
            })->values();
        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    /**
     * @throws JsonException
     */
    public function insert(string $table, array $data): void
    {
        $table = $this->assertTable($table);
        $payload = $this->payload($table, $data);

        $this->mutate(
            fn () => DB::connection()->executeProcedure('IMAJ_APP_PKG.INSERT_ROW', [
                'p_table' => $table,
                'p_payload' => $payload,
            ]),
            'insert',
            $table,
        );
    }

    /**
     * @throws JsonException
     */
    public function update(string $table, int $id, array $data): void
    {
        $table = $this->assertTable($table);
        $payload = $this->payload($table, $data);

        $this->mutate(
            fn () => DB::connection()->executeProcedure('IMAJ_APP_PKG.UPDATE_ROW', [
                'p_table' => $table,
                'p_id' => $id,
                'p_payload' => $payload,
            ]),
            'update',
            $table,
        );
    }

    public function delete(string $table, int $id): void
    {
        $table = $this->assertTable($table);

        $this->mutate(
            fn () => DB::connection()->executeProcedure('IMAJ_APP_PKG.DELETE_ROW', [
                'p_table' => $table,
                'p_id' => $id,
            ]),
            'delete',
            $table,
        );
    }

    public function options(string $table, string $labelColumn, string $valueColumn = 'ID'): array
    {
        return collect($this->rows($table))
            ->mapWithKeys(fn (object $row): array => [$row->{$valueColumn} => $row->{$labelColumn}])
            ->all();
    }

    public function runManager(string $procedure, array $bindings): void
    {
        if (! in_array($procedure, self::MANAGERS, true)) {
            throw new InvalidArgumentException('Gestor PL/SQL no habilitado.');
        }

        $this->mutate(
            fn () => DB::connection()->executeProcedure('IMAJ_APP_PKG.'.$procedure, $bindings),
            'manager',
            $procedure,
        );
    }

    /**
     * @return array<int, object>
     */
    public function report(string $procedure): array
    {
        if (! in_array($procedure, self::REPORTS, true)) {
            throw new InvalidArgumentException('Reporte PL/SQL no habilitado.');
        }

        $rows = DB::connection()->executeProcedureWithCursor(
            'IMAJ_APP_PKG.'.$procedure,
            [],
            ':p_cursor',
        );

        return $this->normalizeColumnNames($rows);
    }

    /**
     * @throws JsonException
     */
    private function payload(string $table, array $data): string
    {
        if (strtoupper($table) === 'IMAJ_USUARIOS' && array_key_exists('PASSWORD_HASH', $data)) {
            $data['PASSWORD_HASH'] = (string) $data['PASSWORD_HASH'];
        }

        $cleaned = array_filter(
            $data,
            static fn ($value): bool => $value !== null && $value !== '',
        );

        return json_encode($cleaned, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param  array<int, object>  $rows
     * @return array<int, object>
     */
    private function hideCredentials(string $table, array $rows): array
    {
        if ($table !== 'IMAJ_USUARIOS') {
            return $rows;
        }

        foreach ($rows as $row) {
            unset($row->PASSWORD_HASH, $row->{'CONTRASEÑA'});
        }

        return $rows;
    }

    private function assertTable(string $table): string
    {
        $table = strtoupper($table);

        if (! in_array($table, self::TABLES, true)) {
            throw new InvalidArgumentException('Tabla IMAJ no habilitada para esta operación.');
        }

        return $table;
    }

    private function normalizeColumnNames(array $rows): array
    {
        return array_map(function (object $row): object {
            $normalizedRow = new \stdClass();

            foreach ($row as $column => $value) {
                $normalizedColumn = strtoupper($column);
                $normalizedRow->{$normalizedColumn} = $value;
            }

            return $normalizedRow;
        }, $rows);
    }

    private function mutate(callable $operation, string $action, string $resource): void
    {
        try {
            DB::transaction($operation);
        } catch (PDOException $exception) {
            $message = $exception->getMessage();

            if (str_contains($message, 'ORA-00001')) {
                throw ValidationException::withMessages(['registro' => 'Ya existe un registro con esos datos.']);
            }

            if (str_contains($message, 'ORA-02291')) {
                throw ValidationException::withMessages(['registro' => 'Selecciona una referencia válida para este registro.']);
            }

            if (str_contains($message, 'ORA-02292')) {
                throw ValidationException::withMessages(['registro' => 'No se puede eliminar porque existen registros relacionados.']);
            }

            if (preg_match('/ORA-200\d\d:\s*([^\r\n]+)/', $message, $matches) === 1) {
                throw ValidationException::withMessages(['registro' => trim($matches[1])]);
            }

            throw $exception;
        }

        OracleOperationCompleted::dispatch($action, $resource, Auth::id());
    }
}
