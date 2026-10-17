<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('change_request_id')->unique()->constrained('change_requests')->cascadeOnDelete();
            $table->foreignId('assessed_by_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('system_criticality_score');
            $table->unsignedTinyInteger('impact_scope_score');
            $table->unsignedTinyInteger('rollback_readiness_score');
            $table->unsignedTinyInteger('total_score')->index();
            $table->enum('risk_level', ['low', 'medium', 'high'])->index();
            $table->text('mitigation_plan');
            $table->text('rollback_plan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_assessments');
    }
};
