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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile', 14)->unique();
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHERS'])->default('MALE');
            $table->date('dob');
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('password');
            $table->string('avatar');
            $table->text('role_id', 2);
            $table->tinyInteger('twofa_status')->default('1');
            $table->tinyInteger('twofa_config');
            $table->tinyInteger('is_deleted')->default('0');
            $table->enum('account_status', ['PENDING', 'ACTIVE', 'INACTIVE', 'BLOCKED'])->default('PENDING');
            $table->timestamp('created_at');
            $table->string('created_by', 50);
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
