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
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable(); // ID of the payment session
            $table->string('payment_id')->nullable(); // ID of the payment
            $table->string('provider'); // Payment provider (e.g., 'tabby')
            $table->string('status')->default('created'); // Status of the payment
            $table->decimal('amount', 10, 2); // Amount of the payment
            $table->string('currency', 3); // Currency code (e.g., SAR)
            $table->string('buyer_phone'); // Phone number of the buyer
            $table->string('order_reference_id')->nullable(); // Merchant's order reference
            $table->string('merchant_code')->nullable(); // Merchant code for the provider
            $table->text('web_url')->nullable(); // URL for the payment page
            $table->json('response_data')->nullable(); // Full response data from provider
            $table->foreignId('case_record_id')->nullable()->constrained('case_records')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
