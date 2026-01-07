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
        Schema::create('case_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->nullable();
            $table->string('title')->nullable();
            $table->text('details')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->foreignId('case_record_id')->nullable()->constrained('case_records');
            $table->string('judge_name')->nullable();
            $table->string('decision')->nullable();
            $table->date('next_session_date')->nullable();
            $table->foreignId('court_id')->constrained('courts')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
