<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lawyer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('user_type')->default('client'); // client, admin, sub_lawyer
            $table->timestamps();

            $table->unique(['lawyer_id', 'user_id', 'user_type'], 'lawyer_user_type_unique');
            $table->index('user_id');
            $table->index('user_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_users');
    }
};
