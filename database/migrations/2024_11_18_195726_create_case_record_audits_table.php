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
        Schema::create('case_record_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_record_id')->constrained('case_records');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('action');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_record_audits');
    }
};
