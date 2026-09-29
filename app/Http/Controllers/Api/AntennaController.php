<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListAntennasRequest;
use App\Http\Resources\AntennaResource;
use App\Models\Antenna;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AntennaController extends Controller
{
    public function index(ListAntennasRequest $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        $antennas = Antenna::query()
            ->with(['latestIntervention'])
            ->filter([
                'city' => $request->validated('city'),
                'status' => $request->validated('status'),
            ])
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        return AntennaResource::collection($antennas);
    }
}
