# AGENTS.md

## Project

startup-dash — Personal dashboard with a visual automation workflow builder. Users create workflows through a drag-and-drop interface that chains triggers, conditions, and actions.

See `docs/PRD-workflow-builder.md` for full product requirements.

## Stack

- PHP 8.2+, Laravel 12, Filament 5.2
- Alpine.js + Livewire for the visual workflow builder UI
- Tailwind CSS 4, Vite 7
- SQLite (default), supports MySQL/PostgreSQL
- Queue: Laravel Queue (database driver by default)
- Package manager: **pnpm** (not npm)

## Setup

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
pnpm install
pnpm run build
```

## Development

```sh
composer dev
```

Starts four processes concurrently: PHP dev server, queue worker, log tail (pail), and Vite dev server.

## Testing

```sh
composer test
```

PHPUnit with SQLite `:memory:`. Test suites: `tests/Unit/`, `tests/Feature/`.

New features should include tests. Use the model factories in `database/factories/`.

## Code Style

PSR-12 via laravel/pint (Laravel preset):

```sh
./vendor/bin/pint
```

Run pint before committing. Configuration in `pint.json`.

## Architecture

### Workflow Engine — `app/Workflows/`

| Directory | Purpose |
|-----------|---------|
| `Contracts/` | `ActionContract`, `TriggerContract` interfaces |
| `Actions/` | Built-in actions: email, CRUD, HTTP requests, conditions, data transforms, nested workflows |
| `Triggers/` | Manual, schedule, model events (created/updated/deleted), Laravel events |
| `Engine/WorkflowExecutor` | Executes workflows step-by-step with branching support |
| `Engine/VariableResolver` | Resolves `{{placeholder}}` syntax at runtime |
| `WorkflowRegistry` | Static registry of available triggers and actions |

Registration happens in `app/Providers/WorkflowServiceProvider.php`.

### Adding a New Action

1. Create a class in `app/Workflows/Actions/` implementing `ActionContract`
2. Implement `getKey()`, `getLabel()`, `getConfigSchema()`, `execute()`
3. Register it in `WorkflowServiceProvider::registerActions()`

### Adding a New Trigger

1. Create a class in `app/Workflows/Triggers/` implementing `TriggerContract`
2. Implement `getKey()`, `getLabel()`, `getConfigSchema()`, `register()`, `resolve()`
3. Register it in `WorkflowServiceProvider::registerTriggers()`

### Filament Resources — `app/Filament/Resources/`

| Resource | Purpose |
|----------|---------|
| `WorkflowResource` | CRUD + visual builder page |
| `WorkflowRunResource` | Execution history and step details |
| `WorkflowSecretResource` | Encrypted secrets management |

### Visual Builder

- Livewire component: `app/Livewire/WorkflowBuilderCanvas.php`
- Blade views: `resources/views/livewire/workflow-builder-canvas.blade.php`
- Alpine.js handles drag-and-drop and canvas rendering

### Models

All models use ULIDs as primary keys (`HasUlids` trait).

| Model | Table | Notes |
|-------|-------|-------|
| `Workflow` | `workflows` | Steps stored as JSON column |
| `WorkflowRun` | `workflow_runs` | Status: pending, running, completed, failed |
| `WorkflowRunStep` | `workflow_run_steps` | Per-step execution record with input/output |
| `WorkflowSecret` | `workflow_secrets` | Values encrypted via Laravel `Crypt` facade |

### Configuration

`config/workflows.php` — Rate limits, retry policy (3 attempts, exponential backoff), queue settings, max workflow nesting depth (5), model class overrides.

## Conventions

- 4-space indentation (see `.editorconfig`)
- Use pnpm for JS dependencies
- Models use ULIDs, not auto-increment IDs
- JSON columns for flexible config (`trigger_config`, `steps`, `input`, `output`)
- Variable interpolation: `{{trigger.field}}`, `{{step.stepId.output.key}}`, `{{secret.name}}`, `{{now}}`
- Condition actions support branching via `true_steps` / `false_steps` arrays in the step definition
