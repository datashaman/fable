<?php

namespace App\Support\Fable;

use App\Models\Belief;
use App\Models\Claim;
use App\Models\Conflict;
use App\Models\Continuity;
use App\Models\Disclosure;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Goal;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Perspective;
use App\Models\Relationship;
use App\Models\Rule;
use App\Models\Saga;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\Story;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class DomainRegistry
{
    /** @var array<string, class-string<Model>> */
    private const MODELS = [
        'milieu' => Milieu::class,
        'continuity' => Continuity::class,
        'ontology_type' => OntologyType::class,
        'entity' => Entity::class,
        'relationship' => Relationship::class,
        'event' => Event::class,
        'rule' => Rule::class,
        'claim' => Claim::class,
        'belief' => Belief::class,
        'perspective' => Perspective::class,
        'scenario' => Scenario::class,
        'goal' => Goal::class,
        'conflict' => Conflict::class,
        'story' => Story::class,
        'scene' => Scene::class,
        'disclosure' => Disclosure::class,
        'saga' => Saga::class,
    ];

    /** @var array<string, list<string>> */
    private const SEARCH_FIELDS = [
        'milieu' => ['name', 'description', 'genre'],
        'continuity' => ['name', 'description'],
        'ontology_type' => ['key', 'name', 'description'],
        'entity' => ['name', 'description'],
        'relationship' => ['description', 'inverse'],
        'event' => ['name', 'description'],
        'rule' => ['name', 'description'],
        'claim' => ['predicate', 'description', 'object_value'],
        'belief' => ['description'],
        'perspective' => ['name', 'description'],
        'scenario' => ['name', 'premise'],
        'goal' => ['objective', 'motivation', 'stakes'],
        'conflict' => ['description'],
        'story' => ['title'],
        'scene' => ['name', 'description'],
        'disclosure' => ['description'],
        'saga' => ['title'],
    ];

    /** @var array<string, array<string, array{record_type: string, description: string, pivot_fields: array<string, string>, ordered?: bool}>> */
    private const RELATIONS = [
        'event' => [
            'locations' => [
                'record_type' => 'entity',
                'description' => 'Entities representing locations where the event occurs.',
                'pivot_fields' => [],
            ],
            'participants' => [
                'record_type' => 'entity',
                'description' => 'Entities participating in the event, optionally with a role.',
                'pivot_fields' => ['role' => 'string'],
            ],
            'causedBy' => [
                'record_type' => 'event',
                'description' => 'Events that caused this event.',
                'pivot_fields' => [],
            ],
        ],
        'rule' => [
            'applicableTypes' => [
                'record_type' => 'ontology_type',
                'description' => 'Ontology types governed by the rule.',
                'pivot_fields' => [],
            ],
            'applicableEntities' => [
                'record_type' => 'entity',
                'description' => 'Specific entities governed by the rule.',
                'pivot_fields' => [],
            ],
            'exceptions' => [
                'record_type' => 'entity',
                'description' => 'Entities exempt from the rule, optionally with an explanation.',
                'pivot_fields' => ['description' => 'string'],
            ],
        ],
        'perspective' => [
            'beliefs' => [
                'record_type' => 'belief',
                'description' => 'Beliefs included in the viewpoint.',
                'pivot_fields' => [],
            ],
            'knownEntities' => [
                'record_type' => 'entity',
                'description' => 'Entities known from the viewpoint.',
                'pivot_fields' => [],
            ],
            'knownEvents' => [
                'record_type' => 'event',
                'description' => 'Events known from the viewpoint.',
                'pivot_fields' => [],
            ],
        ],
        'scenario' => [
            'participants' => [
                'record_type' => 'entity',
                'description' => 'Entities participating in the scenario, optionally with a role.',
                'pivot_fields' => ['role' => 'string'],
            ],
        ],
        'conflict' => [
            'goals' => [
                'record_type' => 'goal',
                'description' => 'Goals that are incompatible in the conflict.',
                'pivot_fields' => [],
            ],
        ],
        'story' => [
            'events' => [
                'record_type' => 'event',
                'description' => 'World events in narrative presentation order.',
                'pivot_fields' => ['sequence' => 'integer'],
                'ordered' => true,
            ],
            'perspectives' => [
                'record_type' => 'perspective',
                'description' => 'Viewpoints used to present the story.',
                'pivot_fields' => [],
            ],
        ],
        'scene' => [
            'events' => [
                'record_type' => 'event',
                'description' => 'World events presented by the scene.',
                'pivot_fields' => [],
            ],
        ],
        'saga' => [
            'stories' => [
                'record_type' => 'story',
                'description' => 'Stories in collection order.',
                'pivot_fields' => ['sequence' => 'integer'],
                'ordered' => true,
            ],
            'conflicts' => [
                'record_type' => 'conflict',
                'description' => 'Conflicts recurring across the collection.',
                'pivot_fields' => [],
            ],
        ],
    ];

    /** @return list<string> */
    public function types(): array
    {
        return array_keys(self::MODELS);
    }

    /** @return class-string<Model> */
    public function modelClass(string $type): string
    {
        return self::MODELS[$type] ?? throw new InvalidArgumentException("Unknown record type [{$type}].");
    }

    /** @return list<string> */
    public function searchFields(string $type): array
    {
        return self::SEARCH_FIELDS[$type] ?? [];
    }

    /** @return array<string, array{record_type: string, description: string, pivot_fields: array<string, string>, ordered?: bool}> */
    public function relationDefinitions(string $type): array
    {
        return self::RELATIONS[$type] ?? [];
    }

    public function milieuFor(Model $record): Milieu
    {
        if ($record instanceof Milieu) {
            return $record;
        }

        if ($record instanceof Scene) {
            return $record->story()->firstOrFail()->milieu()->firstOrFail();
        }

        return Milieu::query()->findOrFail((int) $record->getAttribute('milieu_id'));
    }

    /** @return array<string, mixed> */
    public function schema(): array
    {
        $records = [];

        foreach (self::MODELS as $type => $class) {
            $model = new $class;
            $records[$type] = [
                'fields' => $model->getFillable(),
                'search_fields' => $this->searchFields($type),
                'relations' => $this->relationDefinitions($type),
                'revisioned' => true,
            ];
        }

        return [
            'record_types' => $records,
            'roles' => ['owner', 'editor', 'viewer'],
            'mutation_contract' => [
                'create' => 'omit id',
                'update' => 'provide id and expected_revision',
                'partial_updates' => true,
                'relations' => 'provided relation arrays replace existing links',
                'deletion' => 'not supported',
            ],
        ];
    }
}
