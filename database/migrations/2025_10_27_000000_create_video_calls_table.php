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
        Schema::create('video_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('case_record_id')->nullable()->constrained('case_records')->nullOnDelete();
            $table->string('session_id')->nullable(); // OpenTok session ID
            $table->text('token')->nullable(); // OpenTok token
            $table->string('api_key')->nullable(); // OpenTok API key
            $table->enum('status', ['pending', 'active', 'ended', 'missed', 'declined'])->default('pending');
            $table->enum('call_type', ['audio', 'video'])->default('video');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->nullable(); // Duration in seconds
            $table->boolean('answered_on_web')->default(false); // Track if answered on web dashboard
            $table->boolean('answered_on_mobile')->default(false); // Track if answered on mobile
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('caller_id');
            $table->index('receiver_id');
            $table->index('case_record_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_calls');
    }
};

