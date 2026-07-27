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
        Schema::create('relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('inverse')->nullable();
            $table->boolean('symmetric')->default(false);
            $table->foreignId('source_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('target_id')->constrained('entities')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->string('started_at')->nullable();
            $table->string('ended_at')->nullable();
            $table->string('canonical_status')->default('proposed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationships');
    }
};
