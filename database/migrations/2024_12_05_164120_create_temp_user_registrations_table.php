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
        Schema::create('temp_user_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('registration_id', 100);
            $table->string('name');
            $table->string('email');
            $table->string('mobile', 14);
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHERS'])->default('MALE');
            $table->date('dob');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('password');
            $table->enum('is_verified', ['PENDING', 'APPROVED'])->default('PENDING');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_user_registrations');
    }
};
