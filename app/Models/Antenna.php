<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AntennaStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Antenna extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => AntennaStatus::class,
        ];
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }

    public function latestIntervention(): HasOne
    {
        return $this->hasOne(Intervention::class)->latestOfMany();
    }

    public function activeIntervention(): HasOne
    {
        return $this->hasOne(Intervention::class)->whereNull('ended_at');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['city']), function (Builder $q) use ($filters): void {
                $q->where('city', 'like', '%'.trim((string) $filters['city']).'%');
            })
            ->when(! empty($filters['status']), function (Builder $q) use ($filters): void {
                $q->where('status', $filters['status']);
            });
    }
}
