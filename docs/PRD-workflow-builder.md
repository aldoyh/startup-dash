# PRD: Visual Automation Workflow Builder

## Product Overview

A visual automation workflow builder for the **startup-dash** personal dashboard, inspired by the [Filament Workflow Engine](https://filamentphp.com/plugins/leek-workflow-engine). Users can create, configure, and manage automation workflows through a drag-and-drop visual interface built on Laravel and Filament.

---

## Problem Statement

Dashboard users need the ability to automate repetitive tasks — sending notifications, updating records, chaining actions based on events — without writing code. A visual workflow builder provides an intuitive interface for defining triggers, conditions, and actions as connected steps.

---

## Goals

1. **Visual Builder** — Drag-and-drop interface to compose workflow steps
2. **Extensible Triggers** — Model events, schedules, manual triggers, custom Laravel events
3. **Extensible Actions** — Email, CRUD operations, HTTP requests, conditional branching, nested workflows
4. **Async Execution** — Queue-based processing with retry and rate limiting
5. **Audit Trail** — Full execution logging with per-step input/output tracking
6. **Secrets Management** — Encrypted storage for API keys and tokens used in workflows

---

## Architecture

### Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 11+ |
| Admin Panel | Filament 3 |
| Queue | Laravel Queue (database/redis driver) |
| Database | MySQL / PostgreSQL / SQLite |
| Frontend Workflow UI | Alpine.js + custom Filament Livewire component |

### Data Models

#### `workflows`
| Column | Type | Description |
|--------|------|-------------|
| id | ULID (PK) | Unique identifier |
| name | string | Human-readable name |
| description | text (nullable) | Workflow purpose |
| is_active | boolean | Enable/disable toggle |
| trigger_type | string | Trigger class identifier |
| trigger_config | JSON | Trigger-specific configuration |
| steps | JSON | Ordered array of action step definitions |
| max_runs_per_minute | int | Rate limit (default: 60) |
| max_concurrent_runs | int | Concurrency limit (default: 10) |
| test_mode | boolean | Dry-run mode flag |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `workflow_runs`
| Column | Type | Description |
|--------|------|-------------|
| id | ULID (PK) | |
| workflow_id | ULID (FK) | Parent workflow |
| status | enum | pending, running, completed, failed |
| trigger_data | JSON | Snapshot of trigger payload |
| started_at | timestamp (nullable) | |
| completed_at | timestamp (nullable) | |
| error | text (nullable) | Failure message |
| is_test | boolean | Test mode execution |
| created_at | timestamp | |
| updated_at | timestamp | |

#### `workflow_run_steps`
| Column | Type | Description |
|--------|------|-------------|
| id | ULID (PK) | |
| workflow_run_id | ULID (FK) | Parent run |
| step_id | string | Step identifier from workflow definition |
| action_type | string | Action class identifier |
| status | enum | pending, running, completed, failed, skipped |
| input | JSON (nullable) | Resolved input data |
| output | JSON (nullable) | Action result data |
| error | text (nullable) | Step failure message |
| started_at | timestamp (nullable) | |
| completed_at | timestamp (nullable) | |
| created_at | timestamp | |

#### `workflow_secrets`
| Column | Type | Description |
|--------|------|-------------|
| id | ULID (PK) | |
| name | string (unique) | Reference key |
| encrypted_value | text | AES-256 encrypted value |
| description | string (nullable) | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Triggers

| Trigger | Key | Config Fields |
|---------|-----|--------------|
| Model Created | `model-created` | model_class |
| Model Updated | `model-updated` | model_class, watched_fields[] |
| Model Deleted | `model-deleted` | model_class |
| Status Changed | `status-changed` | model_class, field, from, to |
| Schedule (Cron) | `schedule` | cron_expression |
| Date Condition | `date-condition` | model_class, date_field, offset_days, direction (before/after) |
| Laravel Event | `event` | event_class |
| Manual | `manual` | (none) |

### Trigger Interface

```php
interface TriggerContract
{
    public function getKey(): string;
    public function getLabel(): string;
    public function getConfigSchema(): array;   // Filament form schema
    public function register(Workflow $workflow): void;
    public function resolve(mixed $event): array; // Returns trigger data
}
```

---

## Actions

| Action | Key | Description |
|--------|-----|-------------|
| Send Email | `send-email` | Send via Laravel Mail |
| Send Notification | `send-notification` | Laravel notification to user |
| Create Record | `create-record` | Insert a new Eloquent model |
| Update Record | `update-record` | Modify the triggering model or related |
| Delete Record | `delete-record` | Soft/hard delete a record |
| HTTP Request | `http-request` | Call external API (GET/POST/PUT/DELETE) |
| Condition Branch | `condition` | Evaluate expression → true/false paths |
| Transform Data | `transform-data` | Map/reshape data between steps |
| Run Workflow | `run-workflow` | Execute another workflow (max depth: 5) |

### Action Interface

```php
interface ActionContract
{
    public function getKey(): string;
    public function getLabel(): string;
    public function getConfigSchema(): array;   // Filament form schema
    public function execute(array $input, WorkflowRunStep $step): array; // Returns output
}
```

---

## Variable Interpolation

Actions support `{{placeholder}}` syntax resolved at runtime:

| Pattern | Resolves To |
|---------|-------------|
| `{{trigger.field_name}}` | Trigger model attribute |
| `{{trigger.relation.field}}` | Related model attribute |
| `{{step.stepId.output.key}}` | Output from a previous step |
| `{{secret.name}}` | Decrypted workflow secret |
| `{{now}}` | Current ISO 8601 timestamp |

---

## Visual Builder UI

### Layout
- **Left sidebar**: Palette of available triggers and actions (draggable)
- **Center canvas**: Workflow step graph rendered as connected cards
- **Right sidebar**: Configuration panel for selected step (Filament form)

### Interactions
- Drag action from palette onto canvas to add a step
- Click a step card to select and configure it
- Connect steps via drag-handles between cards
- Condition steps show two output branches (true / false)
- Reorder steps via drag
- Delete steps via toolbar or keyboard shortcut

### Technical Implementation
- Livewire component (`WorkflowBuilderComponent`) manages state
- Alpine.js handles drag-and-drop, canvas rendering, and connections
- Step configuration forms are dynamically rendered Filament form schemas
- Workflow JSON is saved on each change via Livewire

---

## Execution Engine

1. **Trigger fires** → Dispatches `ExecuteWorkflowJob` to queue
2. **Job creates** `WorkflowRun` (status: pending → running)
3. **For each step** in order:
   a. Create `WorkflowRunStep` (pending → running)
   b. Resolve variable placeholders in step config
   c. Execute action → capture output
   d. Mark step completed (or failed/skipped)
   e. If condition action: follow true/false branch
4. **Mark run** completed or failed
5. **Rate limiting** enforced via Laravel's `RateLimiter`

### Retry Policy
- 3 attempts per step
- Exponential backoff: 60s, 300s, 900s
- Failed steps halt the run (configurable to continue)

### Queue Configuration
- Default queue: `workflows`
- Configurable connection and queue name
- Supports Laravel Horizon for monitoring

---

## Filament Admin Resources

| Resource | Purpose |
|----------|---------|
| `WorkflowResource` | CRUD + visual builder page |
| `WorkflowRunResource` | View execution history and step details |
| `WorkflowSecretResource` | Manage encrypted secrets |

### Workflow Resource Pages
- **List** — Table of workflows with status toggle, last run info
- **Create/Edit** — Name, description, trigger config + visual builder
- **View** — Read-only builder view + recent runs summary

### Run Resource Pages
- **List** — Filterable table of all runs with status badges
- **View** — Timeline of step executions with input/output inspection

---

## Configuration

Published as `config/workflows.php`:

```php
return [
    'models' => [
        'workflow' => \App\Models\Workflow::class,
        'workflow_run' => \App\Models\WorkflowRun::class,
        'workflow_run_step' => \App\Models\WorkflowRunStep::class,
        'workflow_secret' => \App\Models\WorkflowSecret::class,
    ],
    'queue' => [
        'connection' => env('WORKFLOW_QUEUE_CONNECTION', 'default'),
        'name' => env('WORKFLOW_QUEUE_NAME', 'workflows'),
    ],
    'rate_limits' => [
        'max_concurrent_runs' => 10,
        'max_runs_per_minute' => 60,
        'max_global_runs_per_minute' => 100,
    ],
    'retry' => [
        'max_attempts' => 3,
        'backoff' => [60, 300, 900],
    ],
    'execution' => [
        'max_workflow_depth' => 5,
    ],
    'discoverable_models' => [
        app_path('Models'),
    ],
];
```

---

## Security Considerations

- Secrets encrypted with `APP_KEY` via Laravel's `Crypt` facade
- Workflow execution sandboxed: actions cannot access filesystem directly
- HTTP request action respects configurable timeout and allowed domains
- Rate limiting prevents runaway execution
- Authorization via Laravel policies on all resources

---

## Success Metrics

| Metric | Target |
|--------|--------|
| Workflow creation time | < 5 minutes for simple automations |
| Execution latency (trigger → first step) | < 2 seconds |
| Step failure rate | < 1% for built-in actions |
| Builder UI responsiveness | < 100ms interaction feedback |

---

## Milestones

1. **M1 — Foundation**: Laravel + Filament setup, migrations, models
2. **M2 — Engine**: Trigger system, action system, variable interpolation, execution engine
3. **M3 — Builder UI**: Visual workflow builder Livewire/Alpine component
4. **M4 — Resources**: Filament CRUD resources, run history, secrets management
5. **M5 — Polish**: Test mode, audit logging, rate limiting, error handling
