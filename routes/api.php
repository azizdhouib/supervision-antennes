<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AntennaController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'healthy',
    'service' => 'telecom-antenna-api',
    'timestamp' => now()->toIso8601String(),
]));

Route::prefix('v1')->group(function (): void {
    Route::get('/antennas', [AntennaController::class, 'index'])->name('api.v1.antennas.index');
});

Route::get('/antennas', [AntennaController::class, 'index'])->name('api.antennas.index');
