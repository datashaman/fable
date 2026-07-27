<?php

use App\Enums\OntologyCategory;
use App\Models\Milieu;
use App\Models\OntologyType;
use Illuminate\Database\QueryException;

test('an ontology type can be created with default factory state', function () {
    $type = OntologyType::factory()->create();

    expect($type)
        ->category->toBeInstanceOf(OntologyCategory::class)
        ->key->toBeString()
        ->name->toBeString();
});

test('an ontology type belongs to a milieu', function () {
    $milieu = Milieu::factory()->create();
    $type = OntologyType::factory()->for($milieu)->create();

    expect($type->milieu->is($milieu))->toBeTrue()
        ->and($milieu->ontologyTypes->first()->is($type))->toBeTrue();
});

test('a milieu cannot declare the same key twice within a category', function () {
    $milieu = Milieu::factory()->create();
    OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Entity, 'key' => 'character']);

    OntologyType::factory()->for($milieu)->create(['category' => OntologyCategory::Entity, 'key' => 'character']);
})->throws(QueryException::class);
