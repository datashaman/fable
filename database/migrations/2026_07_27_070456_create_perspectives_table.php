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
        Schema::create('perspectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('continuity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('holder_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->json('biases')->nullable();
            $table->string('temporal_position')->nullable();
            $table->text('description')->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perspectives');
    }
};
