<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, list<string>> */
    private const INDEXES = [
        'continuities' => ['milieu_id', 'parent_id'],
        'ontology_types' => ['milieu_id'],
        'entities' => ['milieu_id', 'type_id'],
        'relationships' => ['milieu_id', 'continuity_id', 'type_id', 'source_id', 'target_id'],
        'events' => ['milieu_id', 'continuity_id', 'type_id'],
        'rules' => ['milieu_id', 'type_id'],
        'claims' => ['milieu_id', 'subject_id', 'object_id'],
        'beliefs' => ['milieu_id', 'continuity_id', 'holder_id', 'claim_id', 'source_entity_id'],
        'perspectives' => ['milieu_id', 'continuity_id', 'holder_id'],
        'scenarios' => ['milieu_id'],
        'goals' => ['milieu_id', 'continuity_id', 'scenario_id', 'holder_id'],
        'conflicts' => ['milieu_id', 'continuity_id', 'scenario_id', 'subject_id'],
        'stories' => ['milieu_id', 'continuity_id', 'scenario_id', 'focalizer_id', 'narrator_id'],
        'scenes' => ['story_id'],
        'disclosures' => ['milieu_id', 'continuity_id', 'belief_id', 'scene_id'],
        'sagas' => ['milieu_id', 'continuity_id'],
    ];

    /**
     * Run the migrations.
     *
     * Every domain table is filtered by milieu_id (and usually continuity_id
     * or another reference column) on nearly every read path, but the
     * foreignId()->constrained() columns were never given an explicit
     * index, so Postgres/SQLite fall back to full table scans.
     */
    public function up(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
                foreach ($columns as $column) {
                    $blueprint->index($column);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::INDEXES as $table => $columns) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
                foreach ($columns as $column) {
                    $blueprint->dropIndex([$column]);
                }
            });
        }
    }
};
