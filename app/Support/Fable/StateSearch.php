<?php

namespace App\Support\Fable;

use App\Models\Milieu;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StateSearch
{
    public function __construct(
        private DomainRegistry $registry,
        private StatePresenter $presenter,
    ) {}

    /** @return array<string, mixed> */
    public function search(Milieu $milieu, string $query, ?string $type, int $limit): array
    {
        $types = $type === null ? $this->registry->types() : [$type];
        $results = [];

        foreach ($types as $recordType) {
            $class = $this->registry->modelClass($recordType);
            $fields = $this->registry->searchFields($recordType);

            if ($fields === []) {
                continue;
            }

            /** @var Builder<Model> $builder */
            $builder = $class::query();

            if ($recordType === 'milieu') {
                $builder->whereKey($milieu->id);
            } elseif ($class === Scene::class) {
                $builder->whereHas('story', fn (Builder $story) => $story->where('milieu_id', $milieu->id));
            } else {
                $builder->where('milieu_id', $milieu->id);
            }

            $builder->whereAny($fields, 'like', "%{$query}%");

            foreach ($builder->limit($limit)->get() as $record) {
                $results[] = $this->presenter->record($recordType, $record);
            }
        }

        return ['query' => $query, 'count' => count($results), 'results' => array_slice($results, 0, $limit)];
    }
}
