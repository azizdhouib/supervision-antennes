<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/docs', fn () => view('swagger'))->name('swagger.ui');
Route::get('/api/docs', fn () => view('swagger'));

Route::get('/openapi.yaml', function () {
    $path = public_path('openapi.yaml');
    if (! file_exists($path)) {
        $path = base_path('openapi.yaml');
    }

    return Response::file($path, [
        'Content-Type' => 'text/yaml; charset=utf-8',
    ]);
});
