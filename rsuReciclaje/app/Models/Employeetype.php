<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employeetype extends Model
{
    use HasFactory;

    protected $fillable = ['name','description','active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employeetype_id');
    }
}
