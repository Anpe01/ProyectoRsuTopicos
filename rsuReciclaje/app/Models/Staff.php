<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni', 'first_name', 'last_name', 'email', 'phone',
        'license', 'license_category', 'pin', 'photo',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function functions()
    {
        return $this->belongsToMany(EmployeeFunction::class, 'staff_function');
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_personnel');
    }

    public function runs()
    {
        return $this->belongsToMany(Run::class, 'run_personnel');
    }
}


