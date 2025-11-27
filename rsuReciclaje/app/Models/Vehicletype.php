<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicletype extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'type_id');
    }
}














