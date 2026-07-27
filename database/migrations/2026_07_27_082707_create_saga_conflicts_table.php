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
        Schema::create('saga_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saga_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conflict_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['saga_id', 'conflict_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saga_conflicts');
    }
};
