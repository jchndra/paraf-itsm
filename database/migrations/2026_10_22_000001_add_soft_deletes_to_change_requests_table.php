<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('change_requests', function (Blueprint $table) {
            // Soft delete: RFC tidak pernah dihapus permanen dari database.
            $table->softDeletes();
            $table->foreignId('deleted_by_id')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('deletion_reason')->nullable()->after('deleted_by_id');

            $table->index(['submitted_by_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('change_requests', function (Blueprint $table) {
            $table->dropIndex(['submitted_by_id', 'deleted_at']);
            $table->dropConstrainedForeignId('deleted_by_id');
            $table->dropColumn('deletion_reason');
            $table->dropSoftDeletes();
        });
    }
};
