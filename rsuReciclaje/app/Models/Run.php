<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Run extends Model
{
    protected $table = 'runs';
    
    protected $fillable = [
        'run_date',
        'status',
        'zone_id',
        'shift_id',
        'vehicle_id',
        'group_id',
        'notes'
    ];
    
    protected $casts = [
        'run_date' => 'date',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(PersonnelGroup::class, 'group_id');
    }

    public function personnel(): HasMany
    {
        return $this->hasMany(RunPersonnel::class);
    }

    // Tripulación directa (many-to-many)
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'run_personnel', 'run_id', 'staff_id')
                    ->withPivot('function_id')->withTimestamps();
    }

    public function changes()
    {
        return $this->hasMany(RunChange::class);
    }
}
