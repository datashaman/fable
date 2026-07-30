# Fable Domain Playbook

This is the canonical guide to Fable's narrative domain model, invariants, and workflow — for humans and for AI agents alike. Every agent connecting over MCP is instructed to read this same content (served as the `fable://playbook` resource) before using any other Fable capability.

Fable separates the state of a fictional world from what characters believe and from how a story presents that world. Preserve those boundaries in every operation.

## Glossary (quick reference)

| Layer | Record type | One-line definition |
| --- | --- | --- |
| World | `milieu` | The root world or setting: genre, tone, themes, time system, spatial scope, technology, supernatural model, lifecycle status. |
| World | `continuity` | One version of a milieu's history; may branch from a parent at a divergence event. |
| World | `ontology_type` | Milieu-specific meaning given to an entity, relationship, event, or rule. |
| Canon | `entity` | A typed thing in the milieu: a person, place, organization, object, or other concept. |
| Canon | `relationship` | A typed, directed connection between a source and target entity within a continuity. |
| Canon | `event` | A typed occurrence within a continuity; may have locations, participants, causal links, and effects. |
| Canon | `rule` | A constraint on the milieu: conditions, requirements, consequences, priority, validity, applicable types/entities, exceptions. |
| Knowledge | `claim` | A proposition about a subject entity (predicate + entity object or literal value) — not itself a belief. |
| Knowledge | `belief` | A holder's stance toward a claim (`accepts`/`rejects`/`uncertain`) in a continuity, with confidence, source, visibility, validity. |
| Knowledge | `perspective` | A packaged viewpoint: holder, biases, temporal position, beliefs, known entities/events. |
| Possibility | `scenario` | A hypothetical setup: initial conditions, participants, tensions, possible outcomes, selection status. |
| Possibility | `goal` | Something an entity wants, in a continuity; may arise from a scenario. |
| Possibility | `conflict` | Incompatible goals in tension, optionally naming a contested subject; tracks escalation/resolution. |
| Narrative | `story` | Selected world events presented in narrative order: form, themes, narration, focalizer, narrator, perspectives. |
| Narrative | `scene` | Structures a story and presents one or more existing events; sequence is presentation order, not world chronology. |
| Narrative | `disclosure` | Records when a belief is revealed to the audience in a scene — audience knowledge, distinct from character knowledge. |
| Narrative | `saga` | Orders stories and references recurring conflicts; groups stories rather than duplicating their events. |

See also the visual diagram: [`docs/fable-domain-model.html`](fable-domain-model.html).

## Domain map

### World scope

- A **milieu** is the root world or setting. It defines genre, tone, themes, time system, spatial scope, technology, supernatural model, and lifecycle status.
- A **continuity** is one version of a milieu's history. A continuity may branch from a parent at a divergence event. World-state records that vary by timeline belong to a continuity.
- An **ontology type** gives milieu-specific meaning to an entity, relationship, event, or rule. Reuse an existing type with the correct category before creating another.

### World state

- An **entity** is a typed thing in the milieu: a person, place, organization, object, or other concept.
- A **relationship** is a typed, directed connection between source and target entities within a continuity. It may declare an inverse, be symmetric, and have a validity interval.
- An **event** is a typed occurrence within a continuity. It can have locations, participants with roles, causal links to other events, and effects on world state.
- A **rule** constrains the milieu. It can declare conditions, requirements, consequences, priority, validity, applicable types or entities, and explicit exceptions.

Event effects use these forms:

- `set_attribute`: identify `entity_id`, `attribute`, and `value`.
- `end_relationship`: identify `relationship_id`; `ended_at` may override the event time.
- `create_relationship`: provide the new relationship payload; the event supplies milieu, continuity, start time, and canonical status.

### Knowledge and viewpoint

- A **claim** is a proposition about a subject entity, expressed as a predicate plus either an entity object or a literal object value. A claim is not itself someone's belief.
- A **belief** records a holder's stance toward a claim (`accepts`, `rejects`, or `uncertain`) in a continuity, with optional confidence, source, visibility, and validity.
- A **perspective** packages a viewpoint: its holder, biases, temporal position, beliefs, and known entities and events.

Use separate beliefs when characters disagree about the same claim. Do not rewrite world facts merely to represent incomplete knowledge, deception, or an unreliable viewpoint.

### Possibility and dramatic pressure

- A **scenario** is a hypothetical setup with initial conditions, participants, tensions, possible outcomes, and a selection status.
- A **goal** belongs to an entity in a continuity and may arise in a scenario.
- A **conflict** connects incompatible goals and may identify the contested subject. Conflict status tracks escalation and resolution.

### Narrative presentation

- A **story** presents selected world events in narrative order. It chooses form, themes, narration, focalizer, narrator, perspectives, and optionally the scenario it explores.
- A **scene** structures a story and presents one or more existing events. Scene sequence is presentation order, not world chronology.
- A **disclosure** records when a belief is revealed to the audience in a scene. Character knowledge and audience knowledge are separate.
- A **saga** orders stories and references recurring conflicts. It groups stories rather than duplicating their events.

## Required workflow

1. **Establish scope.** Resolve the milieu first, then the relevant continuity. Never silently mix records from different milieus or continuities.
2. **Inspect before creating.** Reuse existing ontology types, entities, claims, events, and other records when they represent the same concept.
3. **Model the world.** Record entities and relationships, then events and their causal or state-changing effects. Apply milieu rules and explicit exceptions.
4. **Model knowledge separately.** Express propositions as claims, character attitudes as beliefs, and bounded viewpoints as perspectives.
5. **Explore possibilities.** Use scenarios for hypotheticals. Attach participant roles, goals, and conflicts before selecting an outcome.
6. **Compose the narrative.** Create a story from existing events, order those events for presentation, organize scenes, choose perspectives, and schedule disclosures.
7. **Group only when useful.** Add stories to a saga when they form a meaningful ordered collection or share recurring conflicts.
8. **Validate consistency.** Check milieu and continuity alignment, ontology categories, causal ordering, temporal validity, participant roles, and references before finishing.

## Data discipline

- Treat time fields as values in the milieu's declared time system; do not assume Gregorian dates.
- Preserve provenance whenever information came from a source, inference, import, or generation step.
- Use canonical status deliberately: `proposed` for unconfirmed material, `canonical` for accepted facts, `disputed` for contested canon, and `obsolete` for superseded material.
- Prefer explicit status transitions over deleting historical or rejected material when provenance or continuity matters.
- Keep world chronology, story event order, scene order, character knowledge, and audience disclosure order distinct.
