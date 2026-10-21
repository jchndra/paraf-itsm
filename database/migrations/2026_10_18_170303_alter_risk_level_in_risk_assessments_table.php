<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            // SQLite tidak mendukung ALTER COLUMN; longgarkan constraint lewat definisi ulang kolom.
            Schema::table('risk_assessments', function (Blueprint $table) {
                $table->dropIndex(['risk_level']);
            });

            DB::statement('ALTER TABLE `risk_assessments` DROP COLUMN `risk_level`');

            Schema::table('risk_assessments', function (Blueprint $table) {
                $table->string('risk_level', 50)->nullable()->index();
            });

            return;
        }

        DB::statement('ALTER TABLE `risk_assessments` MODIFY `risk_level` VARCHAR(50) NULL');
    }

    public function down(): void
    {
        //
    }
};
