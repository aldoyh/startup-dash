<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('trigger_type');
            $table->json('trigger_config')->nullable();
            $table->json('steps')->nullable();
            $table->integer('max_runs_per_minute')->default(60);
            $table->integer('max_concurrent_runs')->default(10);
            $table->boolean('test_mode')->default(false);
            $table->timestamps();
        });

        Schema::create('workflow_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->json('trigger_data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error')->nullable();
            $table->boolean('is_test')->default(false);
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
            $table->index('created_at');
        });

        Schema::create('workflow_run_steps', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
            $table->string('step_id');
            $table->string('action_type');
            $table->string('status')->default('pending');
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['workflow_run_id', 'status']);
        });

        Schema::create('workflow_secrets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->text('encrypted_value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_run_steps');
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('workflow_secrets');
        Schema::dropIfExists('workflows');
    }
};
