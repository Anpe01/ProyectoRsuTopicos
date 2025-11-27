<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MaintenanceSchedule;
use Illuminate\Foundation\Http\FormRequest;

class MaintenanceScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'maintenance_id' => ['required', 'exists:maintenances,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_type' => ['required', 'in:Preventivo,Limpieza,Reparación'],
            'day_of_week' => ['required', 'in:Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'responsible_id' => ['required', 'exists:employees,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $vehicleId = $this->input('vehicle_id');
            $dayOfWeek = $this->input('day_of_week');
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $maintenanceId = $this->input('maintenance_id');
            $scheduleId = $this->route('schedule')?->id;

            if ($vehicleId && $dayOfWeek && $startTime && $endTime && $maintenanceId) {
                // Validar que la hora de fin sea mayor que la hora de inicio
                if ($endTime <= $startTime) {
                    $validator->errors()->add('end_time', 'La hora de fin debe ser mayor que la hora de inicio.');
                    return;
                }

                // Validar solapamiento de horarios DENTRO del mismo mantenimiento
                // IMPORTANTE: Cada mantenimiento es independiente. Los horarios solo se validan
                // dentro del mismo mantenimiento para evitar que un vehículo tenga dos horarios
                // que se solapen en el mismo día dentro del mismo mantenimiento.
                $overlapping = MaintenanceSchedule::where('maintenance_id', $maintenanceId)
                    ->where('vehicle_id', $vehicleId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where(function ($query) use ($startTime, $endTime, $scheduleId) {
                        $query->where(function ($q) use ($startTime, $endTime) {
                            // Caso 1: El nuevo horario empieza dentro de otro horario existente
                            $q->where(function ($q2) use ($startTime, $endTime) {
                                $q2->where('start_time', '<=', $startTime)
                                   ->where('end_time', '>', $startTime);
                            })
                            // Caso 2: El nuevo horario termina dentro de otro horario existente
                            ->orWhere(function ($q2) use ($startTime, $endTime) {
                                $q2->where('start_time', '<', $endTime)
                                   ->where('end_time', '>=', $endTime);
                            })
                            // Caso 3: El nuevo horario contiene completamente a otro horario
                            ->orWhere(function ($q2) use ($startTime, $endTime) {
                                $q2->where('start_time', '>=', $startTime)
                                   ->where('end_time', '<=', $endTime);
                            })
                            // Caso 4: Otro horario contiene completamente al nuevo horario
                            ->orWhere(function ($q2) use ($startTime, $endTime) {
                                $q2->where('start_time', '<=', $startTime)
                                   ->where('end_time', '>=', $endTime);
                            });
                        });
                        // Excluir el horario actual si se está editando
                        if ($scheduleId) {
                            $query->where('id', '!=', $scheduleId);
                        }
                    })
                    ->exists();

                if ($overlapping) {
                    $validator->errors()->add('start_time', 'El horario se solapa con otro horario existente dentro del mismo mantenimiento para el mismo vehículo y día de la semana.');
                    $validator->errors()->add('end_time', 'El horario se solapa con otro horario existente dentro del mismo mantenimiento.');
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'maintenance_id' => 'mantenimiento',
            'vehicle_id' => 'vehículo',
            'maintenance_type' => 'tipo de mantenimiento',
            'day_of_week' => 'día de la semana',
            'start_time' => 'hora de inicio',
            'end_time' => 'hora de fin',
            'responsible_id' => 'responsable',
        ];
    }

    public function messages(): array
    {
        return [
            'maintenance_id.required' => 'Seleccione un mantenimiento.',
            'maintenance_id.exists' => 'El mantenimiento seleccionado no existe.',
            'vehicle_id.required' => 'Seleccione un vehículo.',
            'vehicle_id.exists' => 'El vehículo seleccionado no existe.',
            'maintenance_type.required' => 'Seleccione el tipo de mantenimiento.',
            'maintenance_type.in' => 'El tipo de mantenimiento debe ser Preventivo, Limpieza o Reparación.',
            'day_of_week.required' => 'Seleccione el día de la semana.',
            'day_of_week.in' => 'El día de la semana no es válido.',
            'start_time.required' => 'Ingrese la hora de inicio.',
            'start_time.date_format' => 'La hora de inicio debe tener el formato HH:mm.',
            'end_time.required' => 'Ingrese la hora de fin.',
            'end_time.date_format' => 'La hora de fin debe tener el formato HH:mm.',
            'end_time.after' => 'La hora de fin debe ser mayor que la hora de inicio.',
            'responsible_id.required' => 'Seleccione un responsable.',
            'responsible_id.exists' => 'El responsable seleccionado no existe.',
        ];
    }
}
