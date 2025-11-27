<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('staff')?->id;
        return [
            'dni' => ['required', 'string', 'size:8', 'regex:/^[0-9]{8}$/', Rule::unique('staff', 'dni')->ignore($id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('staff', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'license' => ['nullable', 'string', 'max:50'],
            'license_category' => ['nullable', 'string', 'max:10'],
            'pin' => ['required', 'string', 'max:10', Rule::unique('staff', 'pin')->ignore($id)],
            'photo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'dni' => 'DNI',
            'first_name' => 'nombres',
            'last_name' => 'apellidos',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'license' => 'licencia',
            'license_category' => 'categoría de licencia',
            'pin' => 'PIN',
            'photo' => 'foto',
        ];
    }
}












