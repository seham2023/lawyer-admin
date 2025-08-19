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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('status_id')->nullable()->constrained('statuses');
            $table->foreignId('currency_id')->constrained('currencies');
            $table->foreignId('pay_method_id')->constrained('pay_methods');
            $table->foreignId('payment_id')->nullable()->constrained('payments');
            $table->string('file_path')->nullable();
            $table->string('name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('date_time')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
