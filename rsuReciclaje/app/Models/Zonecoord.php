<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zonecoord extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id', 'latitude', 'longitude', 'sequence',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'sequence' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}














