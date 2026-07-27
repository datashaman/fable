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
        Schema::create('beliefs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('continuity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('holder_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('stance');
            $table->float('confidence')->nullable();
            $table->string('acquired_at')->nullable();
            $table->string('valid_until')->nullable();
            $table->json('source')->nullable();
            $table->string('visibility')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('beliefs');
    }
};
