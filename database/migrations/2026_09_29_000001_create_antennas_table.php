<?php

declare(strict_types=1);

use App\Enums\AntennaStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antennas', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('city', 100)->index();
            $table->string('status', 20)->default(AntennaStatus::UP->value)->index();
            $table->timestamps();

            $table->index(['city', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antennas');
    }
};
