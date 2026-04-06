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
        Schema::table('case_records', function (Blueprint $table) {
            $table->unsignedBigInteger('assigned_lawyer_id')->nullable()->after('user_id');
            $table->index('assigned_lawyer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_records', function (Blueprint $table) {
            $table->dropIndex(['assigned_lawyer_id']);
            $table->dropColumn('assigned_lawyer_id');
        });
    }
};
