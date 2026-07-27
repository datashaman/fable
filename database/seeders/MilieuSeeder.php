<?php

namespace Database\Seeders;

use App\Enums\BeliefStance;
use App\Enums\BeliefVisibility;
use App\Enums\CanonicalStatus;
use App\Enums\ConflictStatus;
use App\Enums\GoalStatus;
use App\Enums\MilieuStatus;
use App\Enums\NarrationMode;
use App\Enums\NarrationPerson;
use App\Enums\NarrationReliability;
use App\Enums\NarrativeCollectionKind;
use App\Enums\NarrativeForm;
use App\Enums\OntologyCategory;
use App\Enums\ScenarioStatus;
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

        $primary = Continuity::create([
            'milieu_id' => $milieu->id,
            'name' => 'Primary',
            'description' => 'The default, canonical timeline of the Imperial Frontier.',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        // This milieu's own ontology: the vocabulary of entity, relationship,
        // event, and rule types it actually uses, rather than a fixed global list.
        $characterType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Entity,
            'key' => 'character',
            'name' => 'Character',
            'description' => 'A person or personified actor within the milieu.',
        ]);

        $groupType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Entity,
            'key' => 'group',
            'name' => 'Group',
            'description' => 'A faction, organisation, or confederation of characters.',
        ]);

        $placeType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Entity,
            'key' => 'place',
            'name' => 'Place',
            'description' => 'A location, whether natural or constructed.',
        ]);

        $objectType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Entity,
            'key' => 'object',
            'name' => 'Object',
            'description' => 'An artefact, item, or relic.',
        ]);

        $memberOfType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Relationship,
            'key' => 'member_of',
            'name' => 'Member Of',
            'description' => 'One entity belongs to an organisational group.',
        ]);

        $ownsType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Relationship,
            'key' => 'owns',
            'name' => 'Owns',
            'description' => 'One entity possesses another.',
        ]);

        $opposesType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Relationship,
            'key' => 'opposes',
            'name' => 'Opposes',
            'description' => 'Two entities stand in active conflict.',
        ]);

        $controlsType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Relationship,
            'key' => 'controls',
            'name' => 'Controls',
            'description' => 'One entity governs or dominates another.',
        ]);

        $conflictEventType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Event,
            'key' => 'conflict',
            'name' => 'Conflict',
            'description' => 'An armed or violent confrontation.',
        ]);

        $conquestEventType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Event,
            'key' => 'conquest',
            'name' => 'Conquest',
            'description' => 'The seizure of a place or polity by force.',
        ]);

        $physicalRuleType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Rule,
            'key' => 'physical',
            'name' => 'Physical',
            'description' => 'A law governing the physical behaviour of the world.',
        ]);

        $legalRuleType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Rule,
            'key' => 'legal',
            'name' => 'Legal',
            'description' => 'A law imposed and enforced by a polity.',
        ]);

        $metaphysicalRuleType = OntologyType::create([
            'milieu_id' => $milieu->id,
            'category' => OntologyCategory::Rule,
            'key' => 'metaphysical',
            'name' => 'Metaphysical',
            'description' => 'A law governing the supernatural order of the world.',
        ]);

        $aria = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $characterType->id,
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
            'type_id' => $characterType->id,
            'name' => 'The Royal Adviser',
            'description' => 'Trusted counsel to the King, secretly ambitious',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $king = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $characterType->id,
            'name' => 'The King',
            'description' => 'Ruler of the Empire, murdered under mysterious circumstances',
            'existed_from' => '430',
            'ended_at' => '487-04-01',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $informant = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $characterType->id,
            'name' => 'The Informant',
            'description' => 'An anonymous source within the palace',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        $ashenFleet = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $groupType->id,
            'name' => 'Ashen Fleet',
            'description' => 'A loose confederation of frontier ships',
            'attributes' => ['group_type' => 'military_confederation', 'ideology' => 'frontier_autonomy', 'membership' => 2400],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $imperialNavy = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $groupType->id,
            'name' => 'Imperial Navy',
            'description' => 'The standing military force of the Empire',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $empire = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $groupType->id,
            'name' => 'The Empire',
            'description' => 'The declining interstellar polity governing the core systems',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $vestra = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $placeType->id,
            'name' => 'Vestra',
            'description' => 'A mining city surrounding an ancient transit gate',
            'attributes' => ['place_type' => 'city', 'population' => 83000, 'climate' => 'arid', 'habitability' => 'artificial'],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $blackKey = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $objectType->id,
            'name' => 'The Black Key',
            'description' => 'An artefact of uncertain origin that Aria carries',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        $voidEngine = Entity::create([
            'milieu_id' => $milieu->id,
            'type_id' => $objectType->id,
            'name' => 'The Void Engine',
            'description' => 'A relic engine capable of gateless travel',
            'canonical_status' => CanonicalStatus::Disputed,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'type_id' => $memberOfType->id,
            'inverse' => 'has_member',
            'source_id' => $aria->id,
            'target_id' => $ashenFleet->id,
            'attributes' => ['since' => 482, 'strength' => 'weak'],
            'started_at' => '482',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'type_id' => $ownsType->id,
            'inverse' => 'owned_by',
            'source_id' => $aria->id,
            'target_id' => $blackKey->id,
            'started_at' => '480',
            'canonical_status' => CanonicalStatus::Proposed,
        ]);

        Relationship::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'type_id' => $opposesType->id,
            'symmetric' => true,
            'source_id' => $ashenFleet->id,
            'target_id' => $empire->id,
            'started_at' => '470',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $empireControlsVestra = Relationship::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'type_id' => $controlsType->id,
            'inverse' => 'controlled_by',
            'source_id' => $empire->id,
            'target_id' => $vestra->id,
            'description' => 'The Empire governs Vestra through an appointed council.',
            'attributes' => ['strength' => 'strong', 'legitimacy' => 'disputed', 'visibility' => 'public'],
            'started_at' => '410',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);

        $blockade = Event::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'type_id' => $conflictEventType->id,
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
            'continuity_id' => $primary->id,
            'type_id' => $conquestEventType->id,
            'name' => 'Capture of Vestra',
            'description' => 'The Ashen Fleet seized Vestra after a three-day blockade.',
            'start_time' => '487-03-14',
            'end_time' => '487-03-17',
            'effects' => [
                ['type' => 'end_relationship', 'relationship_id' => $empireControlsVestra->id],
                ['type' => 'create_relationship', 'relationship' => [
                    'type_id' => $controlsType->id,
                    'inverse' => 'controlled_by',
                    'source_id' => $ashenFleet->id,
                    'target_id' => $vestra->id,
                    'attributes' => ['strength' => 'strong', 'legitimacy' => 'disputed', 'visibility' => 'public'],
                ]],
                ['type' => 'set_attribute', 'entity_id' => $aria->id, 'attribute' => 'legal_status', 'value' => 'wanted'],
            ],
            'tags' => ['military', 'political'],
            'canonical_status' => CanonicalStatus::Canonical,
        ]);
        $capture->locations()->attach($vestra);
        $capture->participants()->attach($ashenFleet->id, ['role' => 'attacker']);
        $capture->participants()->attach($imperialNavy->id, ['role' => 'defender']);
        $capture->participants()->attach($aria->id, ['role' => 'instigator']);
        $capture->causedBy()->attach($blockade);
        $capture->applyEffects();

        $gateTravelRule = Rule::create([
            'milieu_id' => $milieu->id,
            'type_id' => $physicalRuleType->id,
            'name' => 'Gate Travel',
            'description' => 'Faster-than-light travel is possible only through an active gate.',
            'conditions' => ['subject is attempting faster-than-light travel'],
            'requirements' => ['an active gate exists at the origin', 'an active gate exists at the destination'],
            'consequences' => ['travel succeeds when all requirements are satisfied', 'travel fails otherwise'],
            'priority' => 100,
            'valid_from' => '150',
            'canonical_status' => CanonicalStatus::Canonical,
            'provenance' => ['source' => 'worldbuilding_notes', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
        ]);
        $gateTravelRule->applicableTypes()->attach($characterType);
        $gateTravelRule->exceptions()->attach($voidEngine->id, ['description' => 'The Void Engine can travel without gates.']);

        $gateMonopolyRule = Rule::create([
            'milieu_id' => $milieu->id,
            'type_id' => $legalRuleType->id,
            'name' => 'Imperial Gate Monopoly',
            'description' => 'Private ownership of transit gates is prohibited.',
            'consequences' => ['violation may result in confiscation and imprisonment'],
            'priority' => 50,
            'valid_from' => '410',
            'canonical_status' => CanonicalStatus::Canonical,
            'provenance' => ['source' => 'worldbuilding_notes', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
        ]);
        $gateMonopolyRule->applicableEntities()->attach($empire);

        Rule::create([
            'milieu_id' => $milieu->id,
            'type_id' => $metaphysicalRuleType->id,
            'name' => 'Memory Cost',
            'description' => 'Every act of magic permanently transfers one memory.',
            'priority' => 200,
            'valid_from' => '0',
            'canonical_status' => CanonicalStatus::Canonical,
            'provenance' => ['source' => 'worldbuilding_notes', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
        ]);

        $murderClaim = Claim::create([
            'milieu_id' => $milieu->id,
            'subject_id' => $royalAdviser->id,
            'predicate' => 'murdered',
            'object_id' => $king->id,
            'description' => 'The adviser murdered the king.',
        ]);

        $illnessClaim = Claim::create([
            'milieu_id' => $milieu->id,
            'subject_id' => $king->id,
            'predicate' => 'died_of',
            'object_value' => 'illness',
            'description' => "The official account of the King's death.",
        ]);

        $ariaBelief = Belief::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'holder_id' => $aria->id,
            'claim_id' => $murderClaim->id,
            'stance' => BeliefStance::Accepts,
            'confidence' => 0.8,
            'acquired_at' => '487-04-02',
            'source_entity_id' => $informant->id,
            'visibility' => BeliefVisibility::Secret,
            'description' => 'Aria believes the adviser personally killed the king.',
            'canonical_status' => CanonicalStatus::Canonical,
            'provenance' => ['source' => 'chapter_12', 'author' => 'marlinf', 'recorded_at' => '2026-07-27'],
        ]);

        Belief::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'holder_id' => $empire->id,
            'claim_id' => $illnessClaim->id,
            'stance' => BeliefStance::Accepts,
            'acquired_at' => '487-04-01',
            'visibility' => BeliefVisibility::Public,
            'description' => 'The official Imperial account of the King\'s death.',
            'canonical_status' => CanonicalStatus::Disputed,
        ]);

        $ariaPerspective = Perspective::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'name' => "Aria's Perspective",
            'holder_id' => $aria->id,
            'biases' => ['distrusts imperial officials', 'tends to interpret secrecy as evidence of conspiracy'],
            'temporal_position' => '487-06-01',
            'description' => 'The world as Aria currently understands it.',
        ]);
        $ariaPerspective->knownEntities()->attach([$royalAdviser->id, $ashenFleet->id, $vestra->id]);
        $ariaPerspective->knownEvents()->attach($capture);
        $ariaPerspective->beliefs()->attach($ariaBelief);

        $vestraRevolt = Scenario::create([
            'milieu_id' => $milieu->id,
            'name' => 'The Vestra Revolt',
            'premise' => 'What if the Ashen Fleet\'s blockade of Vestra hardens into open revolt against the Empire?',
            'based_on_at' => '487-02-20',
            'initial_conditions' => ['the Ashen Fleet has blockaded Vestra', 'imperial supply lines to the frontier are cut'],
            'tensions' => ['the Empire cannot afford to lose the gate at Vestra', 'the Ashen Fleet lacks the strength for a prolonged siege'],
            'possible_outcomes' => ['the Ashen Fleet seizes Vestra outright', 'the Empire breaks the blockade', 'a negotiated withdrawal'],
            'status' => ScenarioStatus::Selected,
        ]);
        $vestraRevolt->participants()->attach($aria->id, ['role' => 'instigator']);
        $vestraRevolt->participants()->attach($ashenFleet->id, ['role' => 'attacker']);
        $vestraRevolt->participants()->attach($imperialNavy->id, ['role' => 'defender']);

        Goal::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'scenario_id' => $vestraRevolt->id,
            'holder_id' => $aria->id,
            'objective' => 'Free Vestra from imperial rule',
            'motivation' => 'Aria wants to prevent another imperial occupation.',
            'stakes' => ['success' => 'Vestra gains autonomy.', 'failure' => 'Aria and the council are executed.'],
            'status' => GoalStatus::Achieved,
        ]);

        $ashenFleetGoal = Goal::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'scenario_id' => $vestraRevolt->id,
            'holder_id' => $ashenFleet->id,
            'objective' => 'Seize control of Vestra and its gate',
            'motivation' => 'The Fleet needs Vestra\'s gate to secure the frontier against imperial reprisal.',
            'stakes' => ['success' => 'The frontier gains a defensible foothold.', 'failure' => 'The blockade collapses under imperial counterattack.'],
            'status' => GoalStatus::Achieved,
        ]);

        $empireGoal = Goal::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'scenario_id' => $vestraRevolt->id,
            'holder_id' => $empire->id,
            'objective' => 'Retain control of Vestra and its gate',
            'motivation' => 'The Empire cannot afford to lose access to the frontier gate network.',
            'stakes' => ['success' => 'Imperial supply lines remain secure.', 'failure' => 'The frontier slips further from imperial control.'],
            'status' => GoalStatus::Failed,
        ]);

        $vestraConflict = Conflict::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'scenario_id' => $vestraRevolt->id,
            'subject_id' => $vestra->id,
            'description' => 'Both the Ashen Fleet and the Empire seek exclusive control of Vestra and its gate.',
            'status' => ConflictStatus::Resolved,
        ]);
        $vestraConflict->goals()->attach([$ashenFleetGoal->id, $empireGoal->id]);

        $gatebreaker = Story::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'scenario_id' => $vestraRevolt->id,
            'title' => 'Gatebreaker',
            'form' => NarrativeForm::Novella,
            'starts_at' => '487-02-20',
            'ends_at' => '487-03-17',
            'themes' => ['imperial decline', 'defiance', 'the cost of freedom'],
            'narration_person' => NarrationPerson::Third,
            'narration_mode' => NarrationMode::Limited,
            'focalizer_id' => $aria->id,
            'narration_reliability' => NarrationReliability::MostlyReliable,
            'canonical_status' => CanonicalStatus::Canonical,
        ]);
        $gatebreaker->events()->attach($blockade->id, ['sequence' => 0]);
        $gatebreaker->events()->attach($capture->id, ['sequence' => 1]);
        $gatebreaker->perspectives()->attach($ariaPerspective);

        $blockadeScene = Scene::create([
            'story_id' => $gatebreaker->id,
            'name' => 'The Blockade Begins',
            'description' => 'The Ashen Fleet closes the approach to Vestra, and the frontier holds its breath.',
            'sequence' => 0,
        ]);
        $blockadeScene->events()->attach($blockade);

        $captureScene = Scene::create([
            'story_id' => $gatebreaker->id,
            'name' => 'The Gate Falls',
            'description' => 'Aria leads the boarding action that breaks Vestra loose from imperial control.',
            'sequence' => 1,
        ]);
        $captureScene->events()->attach($capture);

        $confessionScene = Scene::create([
            'story_id' => $gatebreaker->id,
            'name' => "The Informant's Confession",
            'description' => 'The informant confirms to Aria what she has only suspected: the adviser murdered the king.',
            'sequence' => 2,
        ]);

        Disclosure::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'belief_id' => $ariaBelief->id,
            'scene_id' => $confessionScene->id,
            'description' => 'The audience learns what Aria has only suspected: the adviser murdered the king.',
        ]);

        $ashenFrontier = Saga::create([
            'milieu_id' => $milieu->id,
            'continuity_id' => $primary->id,
            'title' => 'Ashen Frontier',
            'kind' => NarrativeCollectionKind::Saga,
            'ordering_type' => 'chronological',
            'canonical_status' => CanonicalStatus::Canonical,
        ]);
        $ashenFrontier->stories()->attach($gatebreaker->id, ['sequence' => 0]);
        $ashenFrontier->conflicts()->attach($vestraConflict);
    }
}
