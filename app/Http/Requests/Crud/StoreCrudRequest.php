<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\BaseFormRequest;

class StoreCrudRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $routeName = $this->route()?->getName();

        if (! is_string($routeName)) {
            throw new \LogicException('La solicitud CRUD debe tener una ruta nombrada.');
        }

        return CrudRequestRules::forRoute($routeName);
    }
}
