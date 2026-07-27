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
        Schema::create('continuities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('continuities')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('diverged_from_event_id')->nullable();
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
        Schema::dropIfExists('continuities');
    }
};
