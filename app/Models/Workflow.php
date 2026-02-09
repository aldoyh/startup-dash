<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'trigger_type',
        'trigger_config',
        'steps',
        'max_runs_per_minute',
        'max_concurrent_runs',
        'test_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'test_mode' => 'boolean',
        'trigger_config' => 'array',
        'steps' => 'array',
        'max_runs_per_minute' => 'integer',
        'max_concurrent_runs' => 'integer',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class);
    }

    public function latestRun()
    {
        return $this->hasOne(WorkflowRun::class)->latestOfMany();
    }

    public function getStepsArray(): array
    {
        return $this->steps ?? [];
    }
}
