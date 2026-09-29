<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInterventionRequest;
use App\Http\Resources\InterventionResource;
use App\Models\Intervention;
use App\Services\InterventionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InterventionController extends Controller
{
    public function __construct(
        private readonly InterventionService $interventionService
    ) {}

    public function store(StoreInterventionRequest $request): JsonResponse
    {
        $intervention = $this->interventionService->createIntervention($request->validated());

        return (new InterventionResource($intervention))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function close(Intervention $intervention): InterventionResource
    {
        $closedIntervention = $this->interventionService->closeIntervention($intervention);

        return new InterventionResource($closedIntervention);
    }
}
