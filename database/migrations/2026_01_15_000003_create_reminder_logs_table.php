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
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reminder_id')->constrained()->onDelete('cascade');

            // Delivery details
            $table->string('channel', 50)->comment('Channel used: email, sms, push');
            $table->enum('status', ['success', 'failed'])->comment('Delivery status');

            // Response tracking
            $table->text('response')->nullable()->comment('Response from the delivery service');
            $table->text('error_message')->nullable()->comment('Error message if failed');

            $table->timestamp('sent_at')->comment('When the delivery was attempted');
            $table->timestamp('created_at')->nullable();

            // Indexes for performance
            $table->index('reminder_id', 'idx_reminder_id');
            $table->index('channel', 'idx_channel');
            $table->index('status', 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
