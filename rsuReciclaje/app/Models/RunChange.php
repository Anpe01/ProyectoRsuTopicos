<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunChange extends Model
{
    protected $table = 'run_changes';

    protected $fillable = [
        'run_id',
        'change_type',
        'old_value',
        'new_value',
        'notes'
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }
}
