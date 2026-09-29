<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InterventionPriority;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'antenna_id',
        'description',
        'technician_identity',
        'priority',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => InterventionPriority::class,
            'ended_at' => 'datetime',
        ];
    }

    public function antenna(): BelongsTo
    {
        return $this->belongsTo(Antenna::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('ended_at');
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }
}
