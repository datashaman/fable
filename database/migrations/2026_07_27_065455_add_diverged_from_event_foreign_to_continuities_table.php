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
        Schema::table('continuities', function (Blueprint $table) {
            $table->foreign('diverged_from_event_id')->references('id')->on('events')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('continuities', function (Blueprint $table) {
            $table->dropForeign(['diverged_from_event_id']);
        });
    }
};
