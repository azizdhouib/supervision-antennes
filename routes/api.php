<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AntennaController;
use App\Http\Controllers\Api\InterventionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'healthy',
    'service' => 'telecom-antenna-api',
    'timestamp' => now()->toIso8601String(),
]));

Route::prefix('v1')->group(function (): void {
    Route::get('/antennas', [AntennaController::class, 'index'])->name('api.v1.antennas.index');

    Route::middleware('auth.api_key')->group(function (): void {
        Route::post('/interventions', [InterventionController::class, 'store'])->name('api.v1.interventions.store');
        Route::patch('/interventions/{intervention}/close', [InterventionController::class, 'close'])->name('api.v1.interventions.close');
        Route::patch('/interventions/{intervention}', [InterventionController::class, 'close']);
    });
});

Route::get('/antennas', [AntennaController::class, 'index'])->name('api.antennas.index');
Route::middleware('auth.api_key')->group(function (): void {
    Route::post('/interventions', [InterventionController::class, 'store'])->name('api.interventions.store');
    Route::patch('/interventions/{intervention}/close', [InterventionController::class, 'close'])->name('api.interventions.close');
});
