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
        Schema::create('case_court_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_record_id')->constrained('case_records')->onDelete('cascade');
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');
            $table->date('transfer_date'); // Date when the case was transferred to this court
            $table->string('transfer_reason')->nullable(); // Reason for transfer (e.g., 'Appeal', 'Jurisdiction Change', 'Initial Filing')
            $table->text('notes')->nullable(); // Additional notes about this court assignment
            $table->boolean('is_current')->default(false); // Flag to indicate if this is the current court
            $table->timestamps();

            // Index for better query performance
            $table->index(['case_record_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_court_history');
    }
};
