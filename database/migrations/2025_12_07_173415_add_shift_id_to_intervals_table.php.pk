<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('qestass_app')->table('intervals', function (Blueprint $table) {
            if (!Schema::connection('qestass_app')->hasColumn('intervals', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('to')->constrained('shifts')->onDelete('cascade')->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('qestass_app')->table('intervals', function (Blueprint $table) {
            if (Schema::connection('qestass_app')->hasColumn('intervals', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
        });
    }
};
