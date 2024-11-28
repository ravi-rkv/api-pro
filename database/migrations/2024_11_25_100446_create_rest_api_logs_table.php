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
        Schema::create('rest_api_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('uid')->nullable();
            $table->string('method');
            $table->string('url');
            $table->text('headers');
            $table->text('payload')->nullable();
            $table->text('response')->nullable();
            $table->integer('status_code')->nullable();
            $table->text('ip', 50)->nullable();
            $table->text('duration', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_api_logs');
    }
};
