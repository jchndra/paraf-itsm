<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_number', 50)->unique();
            $table->string('title');
            $table->enum('meeting_type', ['cab', 'ecab'])->default('cab')->index();
            $table->dateTime('scheduled_at')->index();
            $table->string('location_or_link');
            $table->text('minutes_of_meeting')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
