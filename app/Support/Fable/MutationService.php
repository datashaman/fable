<?php

namespace App\Support\Fable;

use App\Exceptions\StaleRevisionException;
use App\Models\Conflict;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\OntologyType;
use App\Models\Perspective;
use App\Models\Rule;
use App\Models\Saga;
use App\Models\Scenario;
use App\Models\Scene;
use App\Models\Story;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MutationService
{
    public function __construct(
        private DomainRegistry $registry,
        private StatePresenter $presenter,
        private ChangeLogger $changeLogger,
    ) {}

    /** @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function save(User $user, string $type, array $arguments, string $toolName): array
    {
        return DB::transaction(function () use ($user, $type, $arguments, $toolName): array {
            $class = $this->registry->modelClass($type);
            $id = Arr::get($arguments, 'id');
            $data = Arr::get($arguments, 'data', []);
            $relations = Arr::get($arguments, 'relations', []);

            if (! is_array($data) || ! is_array($relations)) {
                throw ValidationException::withMessages(['data' => 'Data and relations must be JSON objects.']);
            }

            /** @var Model $record */
            $record = $id === null ? new $class : $class::query()->findOrFail($id);
            $creating = ! $record->exists;

            if ($creating && $record instanceof Milieu) {
                $data['owner_id'] = $user->id;
                $record->owner_id = $user->id;
            }

            $milieu = $creating && $record instanceof Milieu
                ? $record
                : ($creating ? $this->milieuForNewRecord($type, $data) : $this->registry->milieuFor($record));

            if (! $milieu->canEdit($user)) {
                throw new AuthorizationException('An owner or editor role is required to change this milieu.');
            }

            if (! $creating) {
                $expectedRevision = Arr::get($arguments, 'expected_revision');

                if (! is_int($expectedRevision)) {
                    throw ValidationException::withMessages(['expected_revision' => 'The current revision is required for updates.']);
                }

                if ($expectedRevision !== (int) $record->getAttribute('revision')) {
                    throw new StaleRevisionException($expectedRevision, (int) $record->getAttribute('revision'));
                }
            }

            $before = $record->exists ? $record->attributesToArray() : null;
            $record->fill($data);
            $this->validateReferences($type, $record, $data, $milieu);
            $this->saveRevisionedRecord($record, $creating);
            $this->syncRelations($type, $record, $relations, $milieu);
            $record->refresh();

            $changeSet = $this->changeLogger->record(
                milieu: $milieu,
                user: $user,
                toolName: $toolName,
                summary: ($creating ? 'Created ' : 'Updated ').$type.' #'.$record->getKey(),
                recordType: $type,
                recordId: $record->getKey(),
                action: $creating ? 'created' : 'updated',
                before: $before,
                after: $record->attributesToArray(),
                metadata: ['relations' => array_keys($relations)],
            );

            return [
                'ok' => true,
                'change_set_id' => $changeSet->id,
                'record' => $this->presenter->record($type, $record),
            ];
        });
    }

    private function saveRevisionedRecord(Model $record, bool $creating): void
    {
        if (! $creating && ! $record->isDirty()) {
            $record->setAttribute('revision', ((int) $record->getAttribute('revision')) + 1);
            $record->saveQuietly();

            return;
        }

        $record->save();
    }

    /** @param array<string, mixed> $data */
    private function milieuForNewRecord(string $type, array $data): Milieu
    {
        if ($type === 'scene') {
            $storyId = $data['story_id'] ?? null;
            $scene = new Scene(['story_id' => $storyId]);

            return $this->registry->milieuFor($scene);
        }

        return Milieu::query()->findOrFail((int) ($data['milieu_id'] ?? 0));
    }

    /** @param array<string, mixed> $relations */
    private function syncRelations(string $type, Model $record, array $relations, Milieu $milieu): void
    {
        $definitions = $this->registry->relationDefinitions($type);

        foreach ($relations as $name => $items) {
            $definition = $definitions[$name] ?? null;

            if ($definition === null || ! is_array($items)) {
                throw ValidationException::withMessages(["relations.{$name}" => 'This relation is not supported for aggregate sync.']);
            }

            $relation = $this->relationFor($record, $name);

            $sync = [];

            foreach (array_values($items) as $sequence => $item) {
                $id = is_array($item) ? ($item['id'] ?? null) : $item;
                $pivot = is_array($item) ? Arr::except($item, ['id']) : [];

                if ($id === null) {
                    throw ValidationException::withMessages(["relations.{$name}" => 'Every relation item needs an id.']);
                }

                $related = $relation->getRelated()->newQuery()->findOrFail((int) $id);

                if ($this->registry->milieuFor($related)->id !== $milieu->id) {
                    throw ValidationException::withMessages(["relations.{$name}" => 'Cross-milieu references are not allowed.']);
                }

                $unsupportedPivotFields = array_diff(array_keys($pivot), array_keys($definition['pivot_fields']));

                if ($unsupportedPivotFields !== []) {
                    throw ValidationException::withMessages([
                        "relations.{$name}" => 'Unsupported pivot fields: '.implode(', ', $unsupportedPivotFields).'.',
                    ]);
                }

                if ($definition['ordered'] ?? false) {
                    $pivot['sequence'] ??= $sequence + 1;
                }

                $sync[$id] = $pivot;
            }

            $relation->sync($sync);
        }
    }

    /** @param array<string, mixed> $data */
    private function validateReferences(string $type, Model $record, array $data, Milieu $milieu): void
    {
        if (! $record instanceof Milieu && $this->registry->milieuFor($record)->id !== $milieu->id) {
            throw ValidationException::withMessages(['data.milieu_id' => 'A record cannot be moved to another milieu.']);
        }

        foreach ($this->registry->referenceFields() as $field => $referenceType) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $field === 'milieu_id') {
                continue;
            }

            $class = $this->registry->modelClass($referenceType);
            $referenced = $class::query()->findOrFail((int) $data[$field]);

            if ($this->registry->milieuFor($referenced)->id !== $milieu->id) {
                throw ValidationException::withMessages(["data.{$field}" => 'Cross-milieu references are not allowed.']);
            }
        }

        $category = match ($type) {
            'entity' => 'entity',
            'relationship' => 'relationship',
            'event' => 'event',
            'rule' => 'rule',
            default => null,
        };

        if ($category !== null && $record->getAttribute('type_id') !== null) {
            $ontologyType = OntologyType::query()->findOrFail((int) $record->getAttribute('type_id'));

            if ($ontologyType->category->value !== $category) {
                throw ValidationException::withMessages(['data.type_id' => "This aggregate requires an ontology type in the {$category} category."]);
            }
        }
    }

    /** @return BelongsToMany<covariant Model, covariant Model, covariant \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'> */
    private function relationFor(Model $record, string $name): BelongsToMany
    {
        return match (true) {
            $record instanceof Event && $name === 'locations' => $record->locations(),
            $record instanceof Event && $name === 'participants' => $record->participants(),
            $record instanceof Event && $name === 'causedBy' => $record->causedBy(),
            $record instanceof Rule && $name === 'applicableTypes' => $record->applicableTypes(),
            $record instanceof Rule && $name === 'applicableEntities' => $record->applicableEntities(),
            $record instanceof Rule && $name === 'exceptions' => $record->exceptions(),
            $record instanceof Perspective && $name === 'beliefs' => $record->beliefs(),
            $record instanceof Perspective && $name === 'knownEntities' => $record->knownEntities(),
            $record instanceof Perspective && $name === 'knownEvents' => $record->knownEvents(),
            $record instanceof Scenario && $name === 'participants' => $record->participants(),
            $record instanceof Conflict && $name === 'goals' => $record->goals(),
            $record instanceof Story && $name === 'events' => $record->events(),
            $record instanceof Story && $name === 'perspectives' => $record->perspectives(),
            $record instanceof Scene && $name === 'events' => $record->events(),
            $record instanceof Saga && $name === 'stories' => $record->stories(),
            $record instanceof Saga && $name === 'conflicts' => $record->conflicts(),
            default => throw ValidationException::withMessages(["relations.{$name}" => 'This relation cannot be synchronized.']),
        };
    }
}
