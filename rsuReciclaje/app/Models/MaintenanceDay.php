<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_schedule_id', 'date', 'observation', 'image_path', 'executed',
    ];

    protected $casts = [
        'date' => 'date',
        'executed' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class);
    }
}
