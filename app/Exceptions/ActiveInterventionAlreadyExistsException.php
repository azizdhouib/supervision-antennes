<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveInterventionAlreadyExistsException extends Exception
{
    protected $message = 'Une seule intervention active par antenne est autorisée. Une intervention est déjà en cours.';

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'ACTIVE_INTERVENTION_CONFLICT',
        ], Response::HTTP_CONFLICT);
    }
}
