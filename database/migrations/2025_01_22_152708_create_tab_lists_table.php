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
        Schema::create('tab_lists', function (Blueprint $table) {
            $table->id('tab_id');
            $table->string('tab_name', 200);
            $table->string('parent_id')->nullable();
            $table->string('tab_class', 200);
            $table->string('tab_icon', 200)->nullable();
            $table->string('tab_url', 200);
            $table->string('permission_id', 10);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_lists');
    }
};
