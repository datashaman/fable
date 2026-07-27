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
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milieu_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->text('description');
            $table->json('scope')->nullable();
            $table->json('conditions')->nullable();
            $table->json('requirements')->nullable();
            $table->json('consequences')->nullable();
            $table->json('exceptions')->nullable();
            $table->integer('priority')->default(0);
            $table->string('canonical_status')->default('proposed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
