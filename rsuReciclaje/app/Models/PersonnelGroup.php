<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelGroup extends Model
{
    use HasFactory;

    protected $table = 'personnel_groups';

    protected $fillable = [
        'name',
        'zone_id',
        'shift_id',
        'vehicle_id',
        'driver_id',
        'helper1_id',
        'helper2_id',
        'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun',
        'active'
    ];

    protected $casts = [
        'mon' => 'boolean',
        'tue' => 'boolean',
        'wed' => 'boolean',
        'thu' => 'boolean',
        'fri' => 'boolean',
        'sat' => 'boolean',
        'sun' => 'boolean',
        'active' => 'boolean',
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

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function helper1(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'helper1_id');
    }

    public function helper2(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'helper2_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(Run::class, 'group_id');
    }

    // Helper para obtener días de trabajo como array
    public function getDaysOfWeekAttribute(): array
    {
        $days = [];
        if ($this->mon) $days[] = 1;
        if ($this->tue) $days[] = 2;
        if ($this->wed) $days[] = 3;
        if ($this->thu) $days[] = 4;
        if ($this->fri) $days[] = 5;
        if ($this->sat) $days[] = 6;
        if ($this->sun) $days[] = 7;
        return $days;
    }

    // Helper para establecer días de trabajo desde array
    public function setDaysOfWeek(array $days): void
    {
        $this->mon = in_array(1, $days);
        $this->tue = in_array(2, $days);
        $this->wed = in_array(3, $days);
        $this->thu = in_array(4, $days);
        $this->fri = in_array(5, $days);
        $this->sat = in_array(6, $days);
        $this->sun = in_array(7, $days);
    }
}
