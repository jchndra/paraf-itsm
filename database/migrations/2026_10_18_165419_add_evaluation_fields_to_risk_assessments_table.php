<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('risk_assessments', 'impact_level')) {
                $table->string('impact_level')->nullable()->after('risk_level');
            }
            if (! Schema::hasColumn('risk_assessments', 'urgency_level')) {
                $table->string('urgency_level')->nullable()->after('impact_level');
            }
            if (! Schema::hasColumn('risk_assessments', 'classification')) {
                $table->string('classification')->nullable()->after('urgency_level');
            }
            if (! Schema::hasColumn('risk_assessments', 'analysis_notes')) {
                $table->text('analysis_notes')->nullable()->after('classification');
            }
        });
    }

    public function down(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table) {
            $table->dropColumn(['impact_level', 'urgency_level', 'classification', 'analysis_notes']);
        });
    }
};
