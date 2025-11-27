<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id','type','start_date','end_date','salary',
        'department_id','probation_months','active','termination_reason'
    ];

    protected $casts = [
        'start_date'=>'date','end_date'=>'date','active'=>'boolean','salary'=>'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // Scopes
    public function scopeActiveOn($query, $date)
    {
        return $query->where('active', true)
            ->whereDate('start_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            });
    }

    public function scopeEligibleForVacation($query)
    {
        return $query->whereIn('type', \App\Enums\ContractType::eligibleForVacation());
    }
}



