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
        Schema::create('notification_config_associations', function (Blueprint $table) {
            $table->bigIncrements('notify_assoc_id');
            $table->string('notify_id');
            $table->enum('notify_on', ['EMAIL', 'SMS'])->default('EMAIL');
            $table->string('op1', 150)->nullable()->comment('notification sent from ');
            $table->string('op2', 150)->nullable();
            $table->string('op3', 150)->nullable();
            $table->string('op4', 150)->nullable();
            $table->string('op5', 150)->nullable();
            $table->text('content');
            $table->tinyInteger('is_active')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_config_associations');
    }
};
