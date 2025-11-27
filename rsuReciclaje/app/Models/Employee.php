<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // Tabla real
    protected $table = 'employees';
    
    protected $fillable = [
        'dni','function_id','first_name','last_name','birth_date','phone',
        'email','photo_path','password','address','active','pin',
        'license','license_category'
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'birth_date' => 'date',
        'active' => 'boolean',
    ];

    public function jobFunction()
    {
        return $this->belongsTo(JobFunction::class, 'function_id');
    }

    // Accessor de nombre completo
    public function getFullnameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Relaciones que usen la FK 'staff_id' (mantener nombre de columna real)
    public function contracts()   
    { 
        return $this->hasMany(Contract::class, 'employee_id'); 
    }
    
    public function attendances() 
    { 
        return $this->hasMany(Attendance::class, 'employee_id'); 
    }
    
    public function vacations()
    {
        return $this->hasMany(\App\Models\Vacation::class);
    }

    // Relación con funciones mediante pivot staff_function
    public function functions()
    {
        return $this->belongsToMany(FunctionModel::class, 'staff_function', 'staff_id', 'function_id');
    }

    // Helpers para contratos y vacaciones
    public function activeContractOn($date)
    {
        return $this->contracts()->activeOn($date)->first();
    }

    public function hasEligibleVacationContractOn($start, $end): bool
    {
        return $this->contracts()
            ->eligibleForVacation()
            ->activeOn($start)
            ->where(function($q) use ($end) {
                $q->whereNull('end_date')->orWhereDate('end_date','>=',$end);
            })
            ->exists();
    }

    /**
     * Verificar si el empleado tiene contrato activo en un rango de fechas
     */
    public function hasActiveContractInRange($startDate, $endDate): bool
    {
        $current = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            if (!$this->activeContractOn($current->toDateString())) {
                return false;
            }
            $current->addDay();
        }
        
        return true;
    }

    /**
     * Verificar si el empleado está en vacaciones en una fecha específica
     */
    public function isOnVacation($date): bool
    {
        return \App\Models\Vacation::where('employee_id', $this->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Verificar si el empleado está en vacaciones en un rango de fechas
     */
    public function isOnVacationInRange($startDate, $endDate): bool
    {
        return \App\Models\Vacation::where('employee_id', $this->id)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->whereDate('start_date', '<=', $startDate)
                            ->whereDate('end_date', '>=', $endDate);
                      });
            })
            ->exists();
    }

    /**
     * Obtener vacaciones del empleado que se solapan con un rango de fechas
     */
    public function getVacationsInRange($startDate, $endDate): array
    {
        // Convertir a Carbon para comparaciones precisas
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        
        $vacations = \App\Models\Vacation::where('employee_id', $this->id)
            ->where(function($query) use ($start, $end) {
                // Caso 1: La vacación empieza dentro del rango
                $query->where(function($q) use ($start, $end) {
                    $q->whereDate('start_date', '>=', $start->toDateString())
                      ->whereDate('start_date', '<=', $end->toDateString());
                })
                // Caso 2: La vacación termina dentro del rango
                ->orWhere(function($q) use ($start, $end) {
                    $q->whereDate('end_date', '>=', $start->toDateString())
                      ->whereDate('end_date', '<=', $end->toDateString());
                })
                // Caso 3: La vacación contiene completamente el rango
                ->orWhere(function($q) use ($start, $end) {
                    $q->whereDate('start_date', '<=', $start->toDateString())
                      ->whereDate('end_date', '>=', $end->toDateString());
                })
                // Caso 4: El rango contiene completamente la vacación
                ->orWhere(function($q) use ($start, $end) {
                    $q->whereDate('start_date', '>=', $start->toDateString())
                      ->whereDate('end_date', '<=', $end->toDateString());
                });
            })
            ->get();

        return $vacations->map(function($vacation) {
            return [
                'id' => $vacation->id,
                'start_date' => $vacation->start_date ? $vacation->start_date->format('Y-m-d') : null,
                'end_date' => $vacation->end_date ? $vacation->end_date->format('Y-m-d') : null,
                'start_date_formatted' => $vacation->start_date ? $vacation->start_date->format('d/m/Y') : null,
                'end_date_formatted' => $vacation->end_date ? $vacation->end_date->format('d/m/Y') : null,
                'days' => $vacation->days ?? 0,
            ];
        })->toArray();
    }

    /**
     * Verificar si el empleado ya está asignado a otro run en la misma zona y fecha
     */
    public function isAlreadyScheduled($zoneId, $date, $excludeRunId = null): bool
    {
        $query = \App\Models\RunPersonnel::where('staff_id', $this->id)
            ->whereHas('run', function($q) use ($zoneId, $date) {
                $q->where('zone_id', $zoneId)
                  ->whereDate('run_date', $date);
            });
        
        if ($excludeRunId) {
            $query->where('run_id', '!=', $excludeRunId);
        }
        
        return $query->exists();
    }

    /**
     * Verificar si el empleado está disponible para programación
     * (tiene contrato activo, no está en vacaciones, no está duplicado)
     */
    public function isAvailableForScheduling($zoneId, $date, $excludeRunId = null): array
    {
        $issues = [];
        
        // Verificar contrato activo
        if (!$this->activeContractOn($date)) {
            $issues[] = 'No tiene contrato activo en esta fecha';
        }
        
        // Verificar vacaciones - SIEMPRE verificar si tiene vacaciones registradas
        // Si tiene vacaciones registradas, no puede estar disponible independientemente del tipo de contrato
        if ($this->isOnVacation($date)) {
            // Obtener información de la vacación para el mensaje
            $vacation = \App\Models\Vacation::where('employee_id', $this->id)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->first();
            
            if ($vacation) {
                $startFormatted = $vacation->start_date ? $vacation->start_date->format('d/m/Y') : '';
                $endFormatted = $vacation->end_date ? $vacation->end_date->format('d/m/Y') : '';
                $issues[] = "Está en vacaciones del {$startFormatted} al {$endFormatted}";
            } else {
                $issues[] = 'Está en vacaciones en esta fecha';
            }
        }
        
        // Verificar duplicados
        if ($this->isAlreadyScheduled($zoneId, $date, $excludeRunId)) {
            $issues[] = 'Ya está asignado a otro turno en la misma zona y fecha';
        }
        
        return [
            'available' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Scope para filtrar empleados por función (rol) usando function_id
     */
    public function scopeByFunction($query, $functionName)
    {
        return $query->whereHas('jobFunction', function($q) use ($functionName) {
            $q->where('name', $functionName);
        })->orWhereHas('functions', function($q) use ($functionName) {
            $q->where('name', $functionName);
        });
    }

    /**
     * Scope para filtrar conductores
     */
    public function scopeConductors($query)
    {
        $conductorFunction = JobFunction::where('name', 'Conductor')->first();
        if ($conductorFunction) {
            return $query->where('function_id', $conductorFunction->id);
        }
        return $query->whereHas('jobFunction', function($q) {
            $q->where('name', 'Conductor');
        });
    }

    /**
     * Scope para filtrar ayudantes
     */
    public function scopeHelpers($query)
    {
        $helperFunction = JobFunction::where('name', 'Ayudante')->first();
        if ($helperFunction) {
            return $query->where('function_id', $helperFunction->id);
        }
        return $query->whereHas('jobFunction', function($q) {
            $q->where('name', 'Ayudante');
        });
    }
}
