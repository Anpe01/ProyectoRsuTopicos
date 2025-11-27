<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = ['temporal','nombrado','a tiempo completo'];
        return [
            'employee_id'      => ['required','exists:employees,id'],
            'type'             => ['required', Rule::in($types)],
            'start_date'       => ['required','date'],
            'end_date'         => ['nullable','date','after_or_equal:start_date'],
            'salary'           => ['required','numeric','min:0'],
            'department_id'    => ['required','exists:departments,id'],
            'probation_months' => ['required','integer','min:0','max:12'],
            'active'           => ['sometimes','boolean'],
            'termination_reason' => ['nullable','string','min:5'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['active' => $this->boolean('active')]);
    }

    public function withValidator($validator)
    {
        $validator->after(function($v){
            if ($this->filled('active') && $this->boolean('active') === false) {
                if (!$this->filled('termination_reason')) {
                    $v->errors()->add('termination_reason', 'Indica el motivo si el contrato no está activo.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'empleado',
            'type' => 'tipo de contrato',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'salary' => 'salario',
            'department_id' => 'departamento',
            'probation_months' => 'período de prueba',
            'active' => 'activo',
            'termination_reason' => 'motivo de terminación',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Seleccione un empleado.',
            'employee_id.exists' => 'El empleado seleccionado no existe.',
            'type.required' => 'Seleccione un tipo de contrato.',
            'type.in' => 'El tipo de contrato seleccionado no es válido.',
            'start_date.required' => 'Ingrese la fecha de inicio.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'end_date.date' => 'La fecha de finalización debe ser una fecha válida.',
            'end_date.after_or_equal' => 'La fecha de finalización debe ser igual o posterior a la fecha de inicio.',
            'salary.required' => 'Ingrese el salario.',
            'salary.numeric' => 'El salario debe ser un número.',
            'salary.min' => 'El salario debe ser mayor o igual a 0.',
            'department_id.required' => 'Seleccione un departamento.',
            'department_id.exists' => 'El departamento seleccionado no existe.',
            'probation_months.required' => 'Ingrese el período de prueba.',
            'probation_months.integer' => 'El período de prueba debe ser un número entero.',
            'probation_months.min' => 'El período de prueba debe ser mayor o igual a 0.',
            'probation_months.max' => 'El período de prueba debe ser menor o igual a 12.',
            'termination_reason.min' => 'El motivo de terminación debe tener al menos 5 caracteres.',
        ];
    }
}
