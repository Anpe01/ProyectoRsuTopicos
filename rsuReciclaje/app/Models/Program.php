<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','zone_id','vehicle_id','shift_id','conductor_id','start_date','end_date','weekdays','notes'
    ];
    
    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'weekdays'     => 'array', // Cast a array para trabajar con JSON
    ];
    
    // Accesor para mantener compatibilidad con days_of_week
    public function getDaysOfWeekAttribute()
    {
        return $this->weekdays ?? [];
    }
    
    // Mutador para mantener compatibilidad con days_of_week
    public function setDaysOfWeekAttribute($value)
    {
        $this->attributes['weekdays'] = is_array($value) ? json_encode($value) : $value;
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function staff()
    {
        return $this->belongsToMany(
            Employee::class, 
            'program_personnel',
            'program_id',
            'staff_id'
        )->withPivot('role');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }
}


