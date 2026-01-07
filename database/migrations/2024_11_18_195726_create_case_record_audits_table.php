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
        Schema::create('case_record_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_record_id')->constrained('case_records');
            $table->unsignedBigInteger('user_id')->nullable();

            // Add new columns for detailed tracking
            $table->string('event_type')->default('updated'); // created, updated, deleted
            $table->string('field_name')->nullable(); // Which field changed
            $table->text('old_value')->nullable(); // Previous value
            $table->text('new_value')->nullable(); // New value
            $table->json('metadata')->nullable(); // Additional context
            $table->ipAddress('ip_address')->nullable(); // User's IP

            // Add index for better query performance
            $table->index(['case_record_id', 'created_at']);
            $table->index('field_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_record_audits');
    }
};
