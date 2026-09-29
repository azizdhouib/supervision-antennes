<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InterventionAlreadyEndedException extends Exception
{
    protected $message = 'Cette intervention est déjà clôturée.';

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'INTERVENTION_ALREADY_ENDED',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
