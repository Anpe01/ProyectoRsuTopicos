<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Maintenance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maintenanceId = $this->route('maintenance')?->id;

        return [
            'name' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $maintenanceId = $this->route('maintenance')?->id;

            if ($startDate && $endDate) {
                // Validar que la fecha de fin no sea menor que la fecha de inicio
                if ($endDate < $startDate) {
                    $validator->errors()->add('end_date', 'La fecha de fin no puede ser menor que la fecha de inicio.');
                    return;
                }

                // Validar que no haya solapamiento de fechas con otros mantenimientos
                // No debe ser posible registrar dos mantenimientos que se solapen en el tiempo
                $overlapping = Maintenance::where(function ($query) use ($startDate, $endDate, $maintenanceId) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        // Caso 1: El nuevo mantenimiento empieza dentro de otro
                        $q->where(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                               ->where('end_date', '>=', $startDate);
                        })
                        // Caso 2: El nuevo mantenimiento termina dentro de otro
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $endDate)
                               ->where('end_date', '>=', $endDate);
                        })
                        // Caso 3: El nuevo mantenimiento contiene completamente a otro
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '>=', $startDate)
                               ->where('end_date', '<=', $endDate);
                        })
                        // Caso 4: Otro mantenimiento contiene completamente al nuevo
                        ->orWhere(function ($q2) use ($startDate, $endDate) {
                            $q2->where('start_date', '<=', $startDate)
                               ->where('end_date', '>=', $endDate);
                        });
                    });
                    // Excluir el mantenimiento actual si se está editando
                    if ($maintenanceId) {
                        $query->where('id', '!=', $maintenanceId);
                    }
                })->exists();

                if ($overlapping) {
                    $validator->errors()->add('start_date', 'Las fechas se solapan con otro mantenimiento existente. No debe ser posible registrar dos mantenimientos que se solapen en el tiempo.');
                    $validator->errors()->add('end_date', 'Las fechas se solapan con otro mantenimiento existente.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingrese el nombre del mantenimiento.',
            'start_date.required' => 'Seleccione la fecha de inicio.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',
            'end_date.required' => 'Seleccione la fecha de fin.',
            'end_date.date' => 'La fecha de fin debe ser una fecha válida.',
            'end_date.after_or_equal' => 'La fecha de fin debe ser mayor o igual a la fecha de inicio.',
        ];
    }
}
