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
        Schema::create('sagas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('continuity_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('kind')->default('saga');
            $table->json('overarching_conflicts')->nullable();
            $table->string('ordering_type')->nullable();
            $table->string('canonical_status')->default('proposed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sagas');
    }
};
