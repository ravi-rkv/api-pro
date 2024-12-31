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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uid', 50)->nullable();
            $table->string('notify_id', 10);
            $table->string('notify_assoc_id', 10);
            $table->string('sent_on', 100);
            $table->string('identifier', 100);
            $table->string('extra_identifier', 100)->nullable();
            $table->string('otp', 10);
            $table->text('content');
            $table->integer('sent_count')->default('1');
            $table->tinyInteger('is_valid')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
