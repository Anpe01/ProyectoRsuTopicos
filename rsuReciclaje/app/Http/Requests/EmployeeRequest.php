<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('employee')?->id; // null en create
        $eighteen = now()->subYears(18)->toDateString();
        $passwordRules = $id ? ['nullable', 'min:8'] : ['required', 'min:8'];

        return [
            'dni'         => ['required','digits:8', Rule::unique('employees','dni')->ignore($id)],
            'function_id' => ['required','exists:functions,id'],
            'first_name'  => ['required','string','min:2','max:120'],
            'last_name'   => ['required','string','min:2','max:120'],
            'birth_date'  => ['required','date','before:-18 years'],
            'phone'       => ['nullable','string','max:20'],
            'email'       => ['nullable','email','max:150', Rule::unique('employees','email')->ignore($id)],
            'password'    => $passwordRules,
            'address'     => ['nullable','string','max:255'],
            'active'      => ['sometimes','boolean'],
            'pin'         => ['nullable','string','max:10'],
            'photo'       => ['nullable','image','mimes:jpg,jpeg,png','max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'dni' => 'dni',
            'function_id' => 'tipo de empleado',
            'first_name' => 'nombres',
            'last_name' => 'apellidos',
            'birth_date' => 'fecha de nacimiento',
            'phone' => 'teléfono',
            'email' => 'email',
            'photo' => 'fotografía',
            'password' => 'contraseña',
            'address' => 'dirección',
            'active' => 'activo',
            'pin' => 'pin',
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required' => 'Ingrese el DNI del empleado.',
            'dni.digits' => 'El DNI debe tener 8 dígitos.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'function_id.required' => 'Seleccione un tipo de empleado.',
            'function_id.exists' => 'El tipo de empleado seleccionado no existe.',
            'first_name.required' => 'Ingrese los nombres del empleado.',
            'first_name.min' => 'Los nombres deben tener al menos 2 caracteres.',
            'first_name.max' => 'Los nombres no pueden tener más de 120 caracteres.',
            'last_name.required' => 'Ingrese los apellidos del empleado.',
            'last_name.min' => 'Los apellidos deben tener al menos 2 caracteres.',
            'last_name.max' => 'Los apellidos no pueden tener más de 120 caracteres.',
            'birth_date.required' => 'Ingrese la fecha de nacimiento.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before_or_equal' => 'El empleado debe ser mayor de 18 años.',
            'phone.regex' => 'El formato del teléfono no es válido.',
            'email.email' => 'El email no tiene un formato válido.',
            'email.max' => 'El email no puede tener más de 120 caracteres.',
            'email.unique' => 'Este email ya está registrado.',
            'photo.image' => 'La fotografía debe ser una imagen (JPG o PNG).',
            'photo.mimes' => 'La fotografía debe ser JPG o PNG.',
            'photo.max' => 'La fotografía no puede ser mayor a 2MB.',
            'password.required' => 'Ingrese la contraseña.',
            'password.min' => 'La contraseña debe tener mínimo 8 caracteres.',
            'address.min' => 'La dirección debe tener al menos 20 caracteres.',
            'address.max' => 'La dirección no puede tener más de 255 caracteres.',
            'pin.min' => 'El PIN debe tener al menos 4 caracteres.',
            'pin.max' => 'El PIN no puede tener más de 10 caracteres.',
            'pin.unique' => 'Este PIN ya está en uso.',
        ];
    }
}
