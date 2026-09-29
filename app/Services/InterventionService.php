<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AntennaStatus;
use App\Enums\InterventionPriority;
use App\Exceptions\ActiveInterventionAlreadyExistsException;
use App\Exceptions\InterventionAlreadyEndedException;
use App\Models\Antenna;
use App\Models\Intervention;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class InterventionService
{
    public function createIntervention(array $data): Intervention
    {
        return DB::transaction(function () use ($data): Intervention {
            $antenna = Antenna::query()
                ->lockForUpdate()
                ->findOrFail((int) $data['antenna_id']);

            $hasActive = $antenna->interventions()
                ->whereNull('ended_at')
                ->exists();

            if ($hasActive) {
                throw new ActiveInterventionAlreadyExistsException(
                    sprintf("L'antenne '%s' (ID: %d) possède déjà une intervention en cours.", $antenna->name, $antenna->id)
                );
            }

            try {
                $priority = $data['priority'] instanceof InterventionPriority
                    ? $data['priority']
                    : InterventionPriority::from((string) $data['priority']);

                $intervention = $antenna->interventions()->create([
                    'description' => $data['description'],
                    'technician_identity' => $data['technician_identity'],
                    'priority' => $priority,
                    'ended_at' => null,
                ]);

                $antenna->update([
                    'status' => AntennaStatus::DOWN,
                ]);

                return $intervention;
            } catch (UniqueConstraintViolationException|QueryException $e) {
                throw new ActiveInterventionAlreadyExistsException(
                    sprintf("L'antenne '%s' (ID: %d) possède déjà une intervention en cours.", $antenna->name, $antenna->id),
                    previous: $e
                );
            }
        });
    }

    public function closeIntervention(Intervention|int $intervention): Intervention
    {
        $interventionId = $intervention instanceof Intervention ? $intervention->id : $intervention;

        return DB::transaction(function () use ($interventionId): Intervention {
            $interventionModel = Intervention::query()
                ->lockForUpdate()
                ->findOrFail($interventionId);

            if (! $interventionModel->isActive()) {
                throw new InterventionAlreadyEndedException(
                    sprintf("L'intervention #%d est déjà clôturée depuis le %s.", $interventionModel->id, $interventionModel->ended_at?->format('d/m/Y H:i'))
                );
            }

            $interventionModel->update([
                'ended_at' => now(),
            ]);

            $antenna = Antenna::query()
                ->lockForUpdate()
                ->findOrFail($interventionModel->antenna_id);

            $hasOtherActive = $antenna->interventions()
                ->whereNull('ended_at')
                ->where('id', '!=', $interventionModel->id)
                ->exists();

            if (! $hasOtherActive) {
                $antenna->update([
                    'status' => AntennaStatus::UP,
                ]);
            }

            return $interventionModel->fresh();
        });
    }
}
