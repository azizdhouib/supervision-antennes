<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Antenna;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $cities = Antenna::query()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $defaultApiKey = (string) config('services.telecom.api_key', 'secret-telecom-api-token-2026');

        return view('dashboard', [
            'cities' => $cities,
            'defaultApiKey' => $defaultApiKey,
        ]);
    }
}
