<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}














