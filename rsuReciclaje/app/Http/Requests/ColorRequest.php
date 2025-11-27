<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('color')?->id;
        $name = $this->input('name');
        $code = $this->input('code');

        return [
            // Validamos el nombre y además aplicamos la unicidad combinada (name+code) aquí,
            // para no depender de un campo artificial que no existe en el formulario
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('colors')->where(function ($query) use ($code) {
                    return $query->where('code', $code);
                })->ignore($id),
            ],
            'code' => ['nullable', 'string', 'max:200'],
            'description' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'code' => 'código',
            'description' => 'descripción',
        ];
    }
}












