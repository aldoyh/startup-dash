# startup-dash

Personal dashboard with a visual automation workflow builder. Create workflows through a drag-and-drop interface that chains triggers, conditions, and actions — no code required.

Built with Laravel 12, Filament 5, Alpine.js, and Tailwind CSS 4.

## Features

- **Visual workflow builder** — Drag-and-drop canvas for composing automation steps
- **Triggers** — Model events, cron schedules, Laravel events, manual execution
- **Actions** — Email, notifications, CRUD operations, HTTP requests, conditional branching, nested workflows
- **Async execution** — Queue-based processing with retry and rate limiting
- **Audit trail** — Per-step execution logging with input/output tracking
- **Secrets management** — Encrypted storage for API keys and tokens

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ with pnpm
- SQLite (default) or MySQL/PostgreSQL

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

Starts the PHP dev server, queue worker, log tail, and Vite concurrently.

## Testing

```sh
composer test
```

## Documentation

- [Product Requirements](docs/PRD-workflow-builder.md) — Full PRD for the workflow builder
- [Agent Instructions](AGENTS.md) — Architecture and conventions for AI agents

## License

[MIT](LICENSE)
