<?php

namespace Database\Seeders;

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use App\Enums\EntityType;
use App\Enums\EventType;
use App\Enums\MilieuStatus;
use App\Enums\RelationshipType;
use App\Enums\RuleType;
use App\Models\Belief;
use App\Models\Entity;
use App\Models\Event;
use App\Models\Milieu;
use App\Models\Perspective;
use App\Models\Relationship;
use App\Models\Rule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MilieuSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $milieu = Milieu::create([
            'name' => 'The Imperial Frontier',
            'description' => 'A declining interstellar empire and its contested border systems',
            'genre' => 'science_fantasy',
            'tone' => ['melancholic', 'politically tense'],
            'themes' => ['imperial decline', 'memory', 'personal loyalty'],
            'current_time' => '487',
            'time_system' => 'imperial_calendar',
            'spatial_scope' => 'twelve_star_systems',
            'technological_level' => 'interstellar',
            'supernatural_model' => 'memory_magic',
            'default_perspective' => 'frontier_inhabitants',
            'status' => MilieuStatus::Evolving,
        ]);

        $aria = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Character,
            'name' => 'Aria Venn',
            'description' => 'A smuggler operating along the imperial frontier',
            'aliases' => ['The Gatebreaker'],
            'attributes' => ['age' => 31, 'occupation' => 'smuggler', 'legal_status' => 'free'],
            'tags' => ['human', 'frontier'],
            'existed_from' => '456',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $royalAdviser = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Character,
            'name' => 'The Royal Adviser',
            'description' => 'Trusted counsel to the King, secretly ambitious',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Character,
            'name' => 'The King',
            'description' => 'Ruler of the Empire, murdered under mysterious circumstances',
            'existed_from' => '430',
            'ended_at' => '487-04-01',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Character,
            'name' => 'The Informant',
            'description' => 'An anonymous source within the palace',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        $ashenFleet = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Group,
            'name' => 'Ashen Fleet',
            'description' => 'A loose confederation of frontier ships',
            'attributes' => ['group_type' => 'military_confederation', 'ideology' => 'frontier_autonomy', 'membership' => 2400],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $imperialNavy = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Group,
            'name' => 'Imperial Navy',
            'description' => 'The standing military force of the Empire',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $empire = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Group,
            'name' => 'The Empire',
            'description' => 'The declining interstellar polity governing the core systems',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $vestra = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Place,
            'name' => 'Vestra',
            'description' => 'A mining city surrounding an ancient transit gate',
            'attributes' => ['place_type' => 'city', 'population' => 83000, 'climate' => 'arid', 'habitability' => 'artificial'],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $blackKey = Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Object,
            'name' => 'The Black Key',
            'description' => 'An artefact of uncertain origin that Aria carries',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        Entity::create([
            'milieu_id' => $milieu->id,
            'type' => EntityType::Object,
            'name' => 'The Void Engine',
            'description' => 'A relic engine capable of gateless travel',
            'canonical_status' => CanonicalStatus::Disputed,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'type' => RelationshipType::MemberOf,
            'inverse' => 'has_member',
            'source_id' => $aria->id,
            'target_id' => $ashenFleet->id,
            'attributes' => ['since' => 482, 'strength' => 'weak'],
            'started_at' => '482',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'type' => RelationshipType::Owns,
            'inverse' => 'owned_by',
            'source_id' => $aria->id,
            'target_id' => $blackKey->id,
            'started_at' => '480',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'type' => RelationshipType::Opposes,
            'symmetric' => true,
            'source_id' => $ashenFleet->id,
            'target_id' => $empire->id,
            'started_at' => '470',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'type' => RelationshipType::Controls,
            'inverse' => 'controlled_by',
            'source_id' => $empire->id,
            'target_id' => $vestra->id,
            'description' => 'The Empire governs Vestra through an appointed council.',
            'attributes' => ['strength' => 'strong', 'legitimacy' => 'disputed', 'visibility' => 'public'],
            'started_at' => '410',
            'ended_at' => '487-03-17',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $blockade = Event::create([
            'milieu_id' => $milieu->id,
            'type' => EventType::Conflict,
            'name' => 'The Frontier Blockade',
            'description' => 'The Ashen Fleet blockaded the approach to Vestra, cutting off imperial supply lines.',
            'start_time' => '487-02-20',
            'end_time' => '487-03-14',
            'tags' => ['military'],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);
        $blockade->locations()->attach($vestra);
        $blockade->participants()->attach($ashenFleet->id, ['role' => 'attacker']);
        $blockade->participants()->attach($imperialNavy->id, ['role' => 'defender']);

        $capture = Event::create([
            'milieu_id' => $milieu->id,
            'type' => EventType::Conquest,
            'name' => 'Capture of Vestra',
            'description' => 'The Ashen Fleet seized Vestra after a three-day blockade.',
            'start_time' => '487-03-14',
            'end_time' => '487-03-17',
            'effects' => [
                ['type' => 'end_relationship', 'relationship' => 'relationship_empire_controls_vestra'],
                ['type' => 'create_relationship', 'relationship' => ['type' => 'controls', 'source' => 'group_ashen_fleet', 'target' => 'place_vestra']],
                ['type' => 'set_attribute', 'entity' => 'character_aria', 'attribute' => 'legal_status', 'value' => 'wanted'],
            ],
            'tags' => ['military', 'political'],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);
        $capture->locations()->attach($vestra);
        $capture->participants()->attach($ashenFleet->id, ['role' => 'attacker']);
        $capture->participants()->attach($imperialNavy->id, ['role' => 'defender']);
        $capture->participants()->attach($aria->id, ['role' => 'instigator']);
        $capture->causedBy()->attach($blockade);

        // Apply the capture's effects to the milieu's current state.
        $aria->update(['attributes' => ['age' => 31, 'occupation' => 'smuggler', 'legal_status' => 'wanted']]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'type' => RelationshipType::Controls,
            'inverse' => 'controlled_by',
            'source_id' => $ashenFleet->id,
            'target_id' => $vestra->id,
            'attributes' => ['strength' => 'strong', 'legitimacy' => 'disputed', 'visibility' => 'public'],
            'started_at' => '487-03-17',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Rule::create([
            'milieu_id' => $milieu->id,
            'type' => RuleType::Physical,
            'name' => 'Gate Travel',
            'description' => 'Faster-than-light travel is possible only through an active gate.',
            'scope' => ['entity_types' => ['character', 'vehicle'], 'places' => ['region_known_space']],
            'conditions' => ['subject is attempting faster-than-light travel'],
            'requirements' => ['an active gate exists at the origin', 'an active gate exists at the destination'],
            'consequences' => ['travel succeeds when all requirements are satisfied', 'travel fails otherwise'],
            'exceptions' => [['entity' => 'object_void_engine', 'description' => 'The Void Engine can travel without gates.']],
            'priority' => 100,
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Rule::create([
            'milieu_id' => $milieu->id,
            'type' => RuleType::Legal,
            'name' => 'Imperial Gate Monopoly',
            'description' => 'Private ownership of transit gates is prohibited.',
            'scope' => ['places' => ['polity_imperial_territory']],
            'consequences' => ['violation may result in confiscation and imprisonment'],
            'priority' => 50,
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Rule::create([
            'milieu_id' => $milieu->id,
            'type' => RuleType::Metaphysical,
            'name' => 'Memory Cost',
            'description' => 'Every act of magic permanently transfers one memory.',
            'priority' => 200,
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $ariaBelief = Belief::create([
            'milieu_id' => $milieu->id,
            'holder_id' => $aria->id,
            'claim' => [
                'subject' => 'character_royal_adviser',
                'predicate' => 'murdered',
                'object' => 'character_king',
            ],
            'stance' => BeliefStance::Accepts,
            'confidence' => 0.8,
            'acquired_at' => '487-04-02',
            'source' => ['type' => 'entity', 'id' => 'character_informant'],
            'visibility' => BeliefVisibility::Secret,
            'description' => 'Aria believes the adviser personally killed the king.',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Belief::create([
            'milieu_id' => $milieu->id,
            'holder_id' => $empire->id,
            'claim' => [
                'subject' => 'character_king',
                'predicate' => 'died_of',
                'object' => 'illness',
            ],
            'stance' => BeliefStance::Accepts,
            'acquired_at' => '487-04-01',
            'visibility' => BeliefVisibility::Public,
            'description' => 'The official Imperial account of the King\'s death.',
            'canonical_status' => CanonicalStatus::Disputed,
        ]);

        $ariaPerspective = Perspective::create([
            'milieu_id' => $milieu->id,
            'name' => "Aria's Perspective",
            'holder_id' => $aria->id,
            'biases' => ['distrusts imperial officials', 'tends to interpret secrecy as evidence of conspiracy'],
            'temporal_position' => '487-06-01',
            'description' => 'The world as Aria currently understands it.',
        ]);
        $ariaPerspective->knownEntities()->attach([$royalAdviser->id, $ashenFleet->id, $vestra->id]);
        $ariaPerspective->knownEvents()->attach($capture);
        $ariaPerspective->beliefs()->attach($ariaBelief);
    }
}
