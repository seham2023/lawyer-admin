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
        Schema::create('expense_checks', function (Blueprint $table) {
            $table->id();
            $table->string('check_number')->nullable(); 
            $table->string('bank_name')->nullable(); 
            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->timestamp('clearance_date')->nullable(); 
            $table->string('deposit_account')->nullable(); 
            $table->foreignId('expense_id')->nullable()->constrained('expenses');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_checks');
    }
};
