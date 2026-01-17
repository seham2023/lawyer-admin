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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Polymorphic relationship to any remindable entity
            $table->string('remindable_type');
            $table->unsignedBigInteger('remindable_id');

            // Reminder details
            $table->enum('reminder_type', ['session', 'event', 'order', 'payment', 'deadline'])
                ->comment('Type of reminder');
            $table->timestamp('scheduled_at')->comment('When to send the reminder');
            $table->timestamp('sent_at')->nullable()->comment('When the reminder was actually sent');

            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])
                ->default('pending')
                ->comment('Current status of the reminder');

            // Delivery channels and metadata
            $table->json('channels')->comment('Channels to send through: email, sms, push');
            $table->json('metadata')->nullable()->comment('Additional data for the reminder');

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id', 'idx_user_id');
            $table->index(['remindable_type', 'remindable_id'], 'idx_remindable');
            $table->index('scheduled_at', 'idx_scheduled_at');
            $table->index('status', 'idx_status');
            $table->index('reminder_type', 'idx_reminder_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
