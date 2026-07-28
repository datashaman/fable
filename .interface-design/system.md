# Fable Interface System

## Direction

Fable is an archivist’s observatory for a worldbuilder who has just directed an agent through MCP and now needs to inspect the resulting milieu. It should feel quiet, exacting, literary, and alive—not like a generic admin dashboard.

The domain vocabulary is milieus, worlds, universes, continuities, ontology, canon, contested knowledge, possibility, narrative order, provenance, and change ledgers. A milieu is the enclosing context and must never be relabeled as a world; it may itself contain worlds or universes. The signature structure is the persistent milieu-strata model:

1. Milieu — continuities and ontology
2. Canon — entities, relationships, events, and rules
3. Knowledge — claims, beliefs, and perspectives
4. Possibility — scenarios, goals, and conflicts
5. Narrative — stories, scenes, disclosures, and sagas

Pair the strata with a visible realtime Recent Changes stream. The signature composition is an atlas margin: titles and narrative content occupy an open reading column while indices, revisions, IDs, counts, and provenance sit at its edge. Prefer a milieu index over KPI cards, strata navigation over generic admin navigation, and the change stream over a notification bell.

## Color and Surfaces

Dark observatory mode is primary; warm vellum light mode is equally supported. Use the named tokens in `resources/css/app.css` rather than introducing isolated colors.

- Carbon and vellum establish the canvas.
- Oxidized brass marks selection, realtime change, focus, and identity.
- Archive red is reserved for errors, contradictions, or disputed state.
- Desaturated ledger green means connected, valid, or successful.
- Observatory blue is reserved for navigable references.
- Primary, secondary, tertiary, and muted ink tokens provide text hierarchy.

Use same-hue surface shifts: canvas → surface 1 → surface 2, with inset controls darker than their parent surface. Avoid gradients, pure-white cards, decorative color, and multiple competing accents.

## Depth, Shape, and Spacing

- Depth strategy: open canvas, whitespace, typography, and isolated surface shifts; no card shadows and no panels around page sections.
- Borders: use low-opacity horizontal rules to separate sequences and one vertical rule for a true margin or timeline. Do not box fields, counts, sections, or nested groups. Brass emphasis is reserved for selection or focus.
- Spacing base: 4px. Use multiples of 4 for gaps and padding; common component padding is 12px, 16px, or 20px.
- Radius: 4px for controls and badges. Content regions and lists remain unboxed and square because their shape comes from composition, not containers.
- Motion: 140–160ms ease-out for interactions. Realtime updates use one restrained 1.6s brass pulse and must respect reduced motion.

## Typography

- Instrument Sans for interface text and readable dense data.
- Native editorial serif stack for milieu names, record titles, and section headings.
- System monospace for IDs, revisions, timestamps, counts, and MCP tool names.
- Eyebrows are compact uppercase labels with wide tracking and brass color.
- Establish hierarchy with family, weight, tracking, and tone—not size alone.

## Reusable Patterns

- **App shell:** sidebar shares the canvas background and is separated by a soft border. It reads as a compact atlas contents page: a small brand, direct Milieu shelf link, ruled current-milieu switcher, ungrouped Overview, the five true multi-item strata, and direct Recent Changes link. Never create headings for singleton groups such as Workspace, Observatory, or Provenance. Navigation rows are 36px high; selection uses a 2px brass inline edge, brass text, a faint brass wash, and square corners rather than a rounded card. It carries the single persistent connection state and account controls.
- **Milieu index:** accessible milieus form a numbered folio list with large names and descriptions in the reading column and quiet counts in the margin. Milieus are never presented as cards or metric grids.
- **Cosmology:** the five state strata form a numbered vertical sequence. Each layer has a short interpretation and inline record-family links; never divide the layer into cells.
- **Continuity branches:** continuities use a node-and-line lineage list with branch origin and state as marginal metadata, not a card grid.
- **Split explorer:** the searchable collection is a narrow index separated by one vertical rule from a reading-oriented detail article. Mobile stacks index then article. Selection uses a brass edge and wash.
- **Collection rows:** show status or stance once, beside the record title. Summary lines contain only complementary descriptive information and never repeat promoted metadata. Never expose database IDs in collection navigation.
- **Canonical status:** represent the fixed canonical/proposed/disputed/obsolete set with check-circle, clock, exclamation-circle, and minus-circle respectively. The clock communicates a proposal awaiting acceptance without resembling an add or edit action. Use ledger green, brass, archive red, and muted ink. The visible word is replaced by the icon; retain the full status in an accessible label and tooltip. In collection rows, normalize every glyph to an 18px box fixed at the row's trailing edge and apply a 4px downward optical offset so the icon centers on the title glyphs. Reserve sparkles for AI-generation semantics.
- **Event rows:** show the complete temporal span as non-shrinking monospace metadata before the description: `start → end`, `From start`, or `Until end`. The description yields width and may truncate; dates never do.
- **Rule rows:** mirror event chronology using the rule's complete validity span (`valid_from` / `valid_until`) as fixed metadata before the description. Keep execution priority on the detail page; it does not belong in collection navigation.
- **Claim rows:** render claims as complete natural-language propositions—`subject predicate object`—using linked entity names or the literal object value and humanizing predicate slugs. Let the statement wrap; keep the optional explanatory description as the only secondary line.
- **Belief rows:** render beliefs as `holder stance: claim`, reusing the full natural-language claim statement. Show the acquisition/validity span as fixed temporal metadata before the explanation, use the canonical-status icon at the trailing edge, and keep confidence on detail.
- **Relationship rows:** use the compact semantic `Source - Type → Target` triple as the title (`↔` for symmetric edges). Set collection titles one type step below ordinary record titles and let them wrap instead of truncating. Never use `Relationship #ID`, the inverse slug, or another storage-oriented fallback as the visible title. Description is the only optional summary line.
- **Record detail:** lead with the title, status, and prose description. Put ID and revision in the atlas margin. Remaining attributes use an open definition list with label and value columns separated only by horizontal rules—never a bordered field matrix. Reference fields show the linked record's human-readable title and use domain labels such as `Type` or `Source`, never storage labels such as `Type ID`; IDs remain secondary metadata. JSON alone may use an inset monospace block.
- **Linked records:** keep each relation label, count, and explanation in the compact two-column header, then let the linked record list span the full reading width below it. Never allow automatic grid placement to confine record links to the narrow label column.
- **Structured values:** associative data such as entity attributes renders as a nested key/value definition list with humanized keys. Simple lists render inline without JSON punctuation; `tags` use compact brass tag chips while aliases and other lists remain quiet comma-separated values. Reserve inset JSON blocks for structurally irregular data that has no meaningful reading order.
- **Entity relationships:** group incoming and outgoing relationships by continuity, using linked continuity names and per-group counts as archive dividers. Within each continuity, order relationships as a timeline from earliest to latest by `started_at`, then `ended_at`; put undated relationships last and use ID only to resolve ties. Show every edge as a readable `Source - Type → Target` statement. The selected entity is subdued; linked counterparts remain prominent. Put canonical status, temporal validity (`From` / `Until`), and relationship ID on a quiet metadata line, followed by optional prose. Use ruled rows, never edge cards or a generic graph panel.
- **Ontology detail:** treat classified records as entries, not graph relationships. Place a flat `Entries` list directly after the type definition with its count, linked title, and subdued type/ID metadata. Do not nest it under `Graph`, `Linked records`, an `Instances` card, or another redundant hierarchy.
- **Ontology index:** group types under the domain categories Entities, Relationships, Events, and Rules. Preserve a stable category order and alphabetize within each group; use section rules and headings rather than containers. Category headings pair their name with a readable `X types` count. Each type is one compact row containing only its name and right-aligned instance count—never repeat the category, key, or database ID.
- **Recent Changes:** chronological MCP-authored entries use a fine line/dot rhythm, tool-name chip, summary, affected records, actor, and timestamp. The stream may occupy a ruled margin or a focused reading column, but never a generic dashboard panel.
- **Realtime state:** always expose Live, Connecting, or Offline. Relevant changes update in place, append to the ledger, and pulse changed records or fields without resetting navigation.
- **Read-only boundary:** communicate read-only operation through the absence of create, edit, save, delete, and collaborator-management controls; do not label the interface “read only” or “MCP managed.” Search, filters, navigation, appearance, and account security remain interactive.
- **States:** every data surface needs deliberate loading, empty, disconnected, forbidden, and error treatments using the same surface and semantic tokens.

Use Flux Free primitives when available and Tailwind for domain-specific composition. Icons clarify actions or state; they are not decoration.
