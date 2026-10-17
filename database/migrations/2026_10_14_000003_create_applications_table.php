<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('criticality_level', ['low', 'medium', 'high'])->default('medium')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
