<?php

declare(strict_types=1);

use App\Enums\InterventionPriority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('antenna_id')
                ->constrained('antennas')
                ->restrictOnDelete();
            $table->text('description');
            $table->string('technician_identity', 150);
            $table->string('priority', 20)->default(InterventionPriority::MEDIUM->value);
            $table->timestamp('ended_at')->nullable()->index();
            $table->timestamps();

            $table->index(['antenna_id', 'ended_at']);
        });

        // Partial unique index to enforce at most one active intervention per antenna
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('CREATE UNIQUE INDEX unique_active_intervention_per_antenna ON interventions (antenna_id) WHERE ended_at IS NULL');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['pgsql', 'sqlite'], true)) {
            DB::statement('DROP INDEX IF EXISTS unique_active_intervention_per_antenna');
        }

        Schema::dropIfExists('interventions');
    }
};
