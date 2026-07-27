<?php

namespace App\Support\Fable;

use App\Models\Continuity;

class ContinuityValidator
{
    /** @return array<string, mixed> */
    public function validate(Continuity $continuity): array
    {
        $issues = [];

        if ($continuity->parent_id !== null && $continuity->parent?->milieu_id !== $continuity->milieu_id) {
            $issues[] = ['code' => 'cross_milieu_parent', 'record' => "continuity:{$continuity->id}"];
        }

        foreach ($continuity->relationships()->with(['source', 'target', 'type'])->get() as $relationship) {
            if ($relationship->source->milieu_id !== $continuity->milieu_id || $relationship->target->milieu_id !== $continuity->milieu_id) {
                $issues[] = ['code' => 'cross_milieu_relationship', 'record' => "relationship:{$relationship->id}"];
            }

            if ($relationship->type->category->value !== 'relationship') {
                $issues[] = ['code' => 'wrong_ontology_category', 'record' => "relationship:{$relationship->id}"];
            }
        }

        foreach ($continuity->events()->with('type')->get() as $event) {
            if ($event->type->category->value !== 'event') {
                $issues[] = ['code' => 'wrong_ontology_category', 'record' => "event:{$event->id}"];
            }
        }

        return [
            'continuity_id' => $continuity->id,
            'valid' => $issues === [],
            'issue_count' => count($issues),
            'issues' => $issues,
        ];
    }
}
