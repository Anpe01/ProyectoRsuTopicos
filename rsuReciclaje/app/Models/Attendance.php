<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['employee_id','attendance_date','period','status','notes'];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    const PERIOD_IN  = 1;
    const PERIOD_OUT = 2;

    const STATUS_PRESENT = 1;
    const STATUS_ABSENT  = 0;
    const STATUS_JUSTIFY = 2; // opcional

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Accessors de etiquetas para la tabla
    public function getPeriodLabelAttribute(): string
    {
        return $this->period === self::PERIOD_OUT ? 'Salida' : 'Entrada';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ABSENT  => 'Falta',
            self::STATUS_JUSTIFY => 'Justificado',
            default              => 'Presente',
        };
    }
}
