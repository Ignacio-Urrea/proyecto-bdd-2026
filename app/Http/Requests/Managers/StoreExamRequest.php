<?php

namespace App\Http\Requests\Managers;

use App\Http\Requests\BaseFormRequest;

class StoreExamRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'EXAMEN_ID' => ['required', 'integer'],
            'ESTADO' => ['required', 'integer'],
            'EJECUTADA' => ['required', 'integer', 'in:0,1'],
        ];
    }
}
