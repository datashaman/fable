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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('continuity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scenario_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('form')->default('story');
            $table->string('starts_at')->nullable();
            $table->string('ends_at')->nullable();
            $table->json('themes')->nullable();
            $table->string('narration_person')->nullable();
            $table->string('narration_mode')->nullable();
            $table->foreignId('focalizer_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('narrator_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->string('narration_reliability')->nullable();
            $table->string('canonical_status')->default('proposed');
            $table->json('provenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
