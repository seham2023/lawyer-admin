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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->json('value');
            $table->timestamps();

            // Unique constraint: one setting per user per key
            $table->unique(['user_id', 'key'], 'unique_user_setting');

            // Indexes for performance
            $table->index('user_id', 'idx_user_id');
            $table->index('key', 'idx_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
