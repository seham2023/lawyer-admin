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
        Schema::connection('qestass_app')->table('room_messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('created_at');
            $table->timestamp('read_at')->nullable()->after('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('qestass_app')->table('room_messages', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at']);
        });
    }
};
