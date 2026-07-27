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
        Schema::create('milieus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('genre')->nullable();
            $table->json('tone')->nullable();
            $table->json('themes')->nullable();
            $table->string('current_time')->nullable();
            $table->string('time_system')->nullable();
            $table->string('spatial_scope')->nullable();
            $table->string('technological_level')->nullable();
            $table->string('supernatural_model')->nullable();
            $table->string('default_perspective')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milieus');
    }
};
