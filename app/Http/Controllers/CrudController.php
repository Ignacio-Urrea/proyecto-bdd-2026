<?php

namespace App\Http\Controllers;

use App\Http\Requests\Crud\CrudRequestRules;
use App\Http\Requests\Crud\StoreCrudRequest;
use App\Http\Requests\Crud\UpdateCrudRequest;
use App\Services\OracleCrudService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class CrudController extends Controller
{
    /** @var class-string<Model> */
    protected string $modelClass;

    protected string $title;

    protected string $routeName;

    protected array $fields = [];

    protected array $hashFields = [];

    protected array $preserveOnEmpty = [];

    public function validationRules(bool $updating = false): array
    {
        return CrudRequestRules::for($this->routeName, $updating);
    }

    public function index(): View
    {
        $modelClass = $this->modelClass;
        $table = (new $modelClass)->getTable();

        return view($this->viewDirectory().'.index', [
            'title' => $this->title,
            'routeName' => $this->routeName,
            'fields' => $this->visibleFields('index'),
            'records' => app(OracleCrudService::class)->paginate($table),
        ]);
    }

    public function create(): View
    {
        return $this->formView(null, 'Crear', 'create');
    }

    public function store(StoreCrudRequest $request): RedirectResponse
    {
        $modelClass = $this->modelClass;
        $data = $this->hashSensitiveFields($request->validated());
        app(OracleCrudService::class)->insert((new $modelClass)->getTable(), $data);

        return redirect()->route($this->routeName.'.index')->with('status', 'Registro creado correctamente.');
    }

    public function edit(int $id): View
    {
        $modelClass = $this->modelClass;
        $record = app(OracleCrudService::class)->find((new $modelClass)->getTable(), $id);

        abort_if($record === null, 404);

        return $this->formView($record, 'Editar', 'update');
    }

    public function show(int $id): View
    {
        $modelClass = $this->modelClass;
        $record = app(OracleCrudService::class)->find((new $modelClass)->getTable(), $id);

        abort_if($record === null, 404);

        return view($this->viewDirectory().'.view', [
            'title' => $this->title,
            'routeName' => $this->routeName,
            'fields' => $this->visibleFields('view'),
            'record' => $record,
        ]);
    }

    public function confirmDelete(int $id): View
    {
        $modelClass = $this->modelClass;
        $record = app(OracleCrudService::class)->find((new $modelClass)->getTable(), $id);

        abort_if($record === null, 404);

        return view($this->viewDirectory().'.delete', [
            'title' => $this->title,
            'routeName' => $this->routeName,
            'fields' => $this->visibleFields('view'),
            'record' => $record,
        ]);
    }

    public function update(UpdateCrudRequest $request, int $id): RedirectResponse
    {
        $modelClass = $this->modelClass;
        $service = app(OracleCrudService::class);
        abort_if($service->find((new $modelClass)->getTable(), $id) === null, 404);
        $data = $request->validated();

        foreach ($this->preserveOnEmpty as $field) {
            if (empty($data[$field])) {
                unset($data[$field]);
            }
        }

        $service->update((new $modelClass)->getTable(), $id, $this->hashSensitiveFields($data));

        return redirect()->route($this->routeName.'.index')->with('status', 'Registro actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $modelClass = $this->modelClass;
        app(OracleCrudService::class)->delete((new $modelClass)->getTable(), $id);

        return redirect()->route($this->routeName.'.index')->with('status', 'Registro eliminado correctamente.');
    }

    private function formView(?object $record, string $action, string $template): View
    {
        $rules = $this->validationRules(updating: $record !== null);
        $fields = array_map(function (array $field) use ($rules): array {
            $fieldRules = $rules[$field['name']] ?? [];
            $field['required'] = in_array('required', $fieldRules, true);

            if (isset($field['options']) && is_callable($field['options'])) {
                $field['options'] = $field['options']();
            }

            return $field;
        }, $this->fields);

        return view($this->viewDirectory().'.'.$template, [
            'title' => $this->title,
            'routeName' => $this->routeName,
            'fields' => $fields,
            'record' => $record,
            'action' => $action,
        ]);
    }

    private function visibleFields(string $context): array
    {
        $visibilityKey = 'show_in_'.$context;

        return array_values(array_filter(
            $this->fields,
            static fn (array $field): bool => $field[$visibilityKey] ?? true,
        ));
    }

    private function viewDirectory(): string
    {
        return Str::kebab(class_basename($this->modelClass));
    }

    private function hashSensitiveFields(array $data): array
    {
        foreach ($this->hashFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $data[$field] = Hash::make($data[$field]);
            }
        }

        return $data;
    }
}
