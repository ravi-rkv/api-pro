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
            $table->string('user_name');
            $table->string('email')->unique();
            $table->string('mobile', 14)->unique();
            $table->string('password');
            $table->string('profile_image');
            $table->tinyInteger('twofa_status')->default('1');
            $table->tinyInteger('twofa_config');
            $table->tinyInteger('is_deleted')->default('0');
            $table->enum('account_status', ['PENDING', 'ACTIVE', 'INACTIVE', 'BLOCKED'])->default('PENDING');
            $table->timestamp('created_at');
            $table->string('created_by', 50);
            $table->timestamp('updated_at');
            $table->string('updated_by', 50);
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
