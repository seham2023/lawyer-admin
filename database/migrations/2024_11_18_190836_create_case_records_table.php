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
        Schema::create('case_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('client_type_id')->constrained('categories');
            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->foreignId('level_id')->nullable()->constrained('levels');
            $table->foreignId('payment_id')->nullable()->constrained('payments');
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('opponent_id')->constrained('opponents');
            $table->foreignId('opponent_lawyer_id')->nullable()->constrained('opponent_lawyers');
            $table->date('start_date');
          
            $table->string('court_name');
            $table->string('court_number');
            $table->string('lawyer_name');
            $table->string('judge_name');
            $table->string('location');
            $table->string('subject');
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
