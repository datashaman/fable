<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('milieus', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        foreach ($this->revisionedTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unsignedInteger('revision')->default(1);

                if ($tableName === 'events') {
                    $table->timestamp('effects_applied_at')->nullable();
                }
            });
        }

        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId !== null) {
            DB::table('milieus')->whereNull('owner_id')->update(['owner_id' => $firstUserId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse($this->revisionedTables()) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = ['revision'];

                if ($tableName === 'events') {
                    $columns[] = 'effects_applied_at';
                }

                $table->dropColumn($columns);
            });
        }

        Schema::table('milieus', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }

    /** @return list<string> */
    private function revisionedTables(): array
    {
        return [
            'milieus', 'continuities', 'ontology_types', 'entities', 'relationships',
            'events', 'rules', 'claims', 'beliefs', 'perspectives', 'scenarios',
            'goals', 'conflicts', 'stories', 'scenes', 'disclosures', 'sagas',
        ];
    }
};
