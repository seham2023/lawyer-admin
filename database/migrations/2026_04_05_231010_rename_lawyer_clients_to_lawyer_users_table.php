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
        if (Schema::hasTable('lawyer_clients')) {
            Schema::rename('lawyer_clients', 'lawyer_users');
        }

        Schema::table('lawyer_users', function (Blueprint $table) {
            if (Schema::hasColumn('lawyer_users', 'client_id')) {
                $table->renameColumn('client_id', 'user_id');
            }
            
            if (!Schema::hasColumn('lawyer_users', 'user_type')) {
                $table->string('user_type')->default('client')->after('user_id');
                $table->index('user_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lawyer_users', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropColumn('user_type');
            $table->renameColumn('user_id', 'client_id');
        });

        Schema::rename('lawyer_users', 'lawyer_clients');
    }
};
