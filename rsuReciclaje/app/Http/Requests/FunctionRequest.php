<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FunctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('function')?->id;
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('functions', 'name')->ignore($id)],
            'description' => ['nullable', 'string'],
            'protected' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'protected' => 'protegido',
        ];
    }
}



