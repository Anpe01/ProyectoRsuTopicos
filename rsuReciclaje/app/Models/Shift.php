<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['name','start_time','end_time','description','active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}



