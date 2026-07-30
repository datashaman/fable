# Fable

Fable is a Laravel application that exposes a **Model Context Protocol (MCP) server** so AI agents can collaboratively build and query serialized narrative worlds — the kind of continuity bible a writers' room keeps by hand, but structured, revisioned, and queryable.

A web UI (Livewire + Flux) lets humans browse the same state agents are editing.

## The domain model

State is organized into layered record types (world → canon → knowledge → possibility → narrative), each scoped to a **milieu** (a story world) and owned/edited by users with an `owner`, `editor`, or `viewer` role. Every record carries a `revision` counter — mutations are optimistic-concurrency-checked via `expected_revision`, and every create/update is written to an append-only `ChangeSet`/`ChangeEntry` audit log.

**[`docs/domain-playbook.md`](docs/domain-playbook.md)** is the canonical guide to this model — glossary, invariants, and required workflow. It's not just human documentation: it's served byte-for-byte to every connecting agent as the `fable://playbook` MCP resource, so read it there rather than here to avoid two versions drifting apart. See also the visual diagram at [`docs/fable-domain-model.html`](docs/fable-domain-model.html).

The core services in `app/Support/Fable/` implement this:

- **`DomainRegistry`** — the single source of truth for record types, their model classes, search fields, and synchronizable relations.
- **`MutationService`** — validates and applies every create/update (cross-milieu reference checks, revision checks, relation sync, change logging).
- **`PresentationRegistry`** — drives how records are queried, titled, and summarized for both the MCP resources and the Livewire UI.
- **`ChangeLogger`** — records the `ChangeSet`/`ChangeEntry` pair for every mutation.

## The MCP server

`app/Mcp/Servers/FableServer.php` exposes tools (`SaveEntity`, `RecordEvent`, `ComposeStory`, ...), resources (`fable://schema`, `fable://workspace`, `fable://milieus/{id}`, ...), and prompts. It's reachable two ways (see `routes/ai.php`):

- **Local (stdio)** — `Mcp::local('fable', ...)`, for connecting an agent CLI directly to this app. Requires `FABLE_MCP_USER_EMAIL` in `.env` to identify which account the server authenticates as.
- **Web** — `Mcp::web('/mcp', ...)` behind `auth:api`, authenticated via Passport OAuth (`Mcp::oauthRoutes()` registers the authorization/token endpoints).

## Local setup

Requires PHP 8.5, Composer, Node 22, and SQLite (the local default — see `config/database.php` for other drivers).

`.env.example` assumes a local TLS domain (`https://fable.test`, matching its `REVERB_*` defaults) — e.g. via [Laravel Herd](https://herd.laravel.com/) or Valet. Point that domain at this project, or edit `APP_URL`/`REVERB_*` to match your own setup, before running the app.

```bash
composer setup        # composer install, .env, app key, migrate, npm install + build
php artisan passport:keys --force   # generate OAuth encryption keys (gitignored, not run by `setup`)
composer dev           # runs the app server, queue worker, Reverb, and Vite together
```

Then visit your configured `APP_URL`, or point an MCP client at the local/web transport described above.

## Verification

```bash
php artisan test --compact              # Pest suite
vendor/bin/pint --dirty --format agent  # code style (use --format agent, not --test, to auto-fix)
vendor/bin/phpstan analyse              # static analysis (Larastan)
```

CI (`.github/workflows/tests.yml`) runs all three via `composer ci:check` on every push/PR.

## Contributing

All changes — including documentation — land via a feature branch and pull request; nothing is committed directly to `main`. See `AGENTS.md` / `CLAUDE.md` for the fuller set of conventions this codebase follows.
