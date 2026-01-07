<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('client_type_id')->constrained('categories');
            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('opponent_id')->nullable()->constrained('opponents');
            $table->foreignId('opponent_lawyer_id')->nullable()->constrained('opponent_lawyers');
            $table->date('start_date');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('lawyer_id')->nullable();
            // $table->string('judge_name')->nullable();
            $table->string('subject')->nullable();
            $table->text('subject_description')->nullable();
            $table->text('notes')->nullable();
            $table->text('contract')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_records');
    }
};
