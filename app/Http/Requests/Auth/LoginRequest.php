<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'NOMBRE_USUARIO' => ['required', 'string', 'max:16'],
            'password' => ['required', 'string'],
        ];
    }
}
