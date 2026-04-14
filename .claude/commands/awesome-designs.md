# /awesome-designs

Apply the **Awesome Designs** skill to the current task.

## What this command does

Activates the project's design system and UI best-practice guidelines, then applies them to the file or component you specify. Use it whenever you want Claude to generate or review UI code with opinionated, high-quality design patterns for this project's stack (Tailwind CSS 4 · Alpine.js · Livewire · Filament 5 · Blade).

## Usage

```
/awesome-designs [target]
```

- **No argument** — Review and improve the last file you discussed or the currently open file.
- **File path** — Apply awesome designs to that specific Blade / Livewire component.
- **Description** — Describe a new component to build from scratch.

## Examples

```
/awesome-designs resources/views/livewire/workflow-builder-canvas.blade.php
/awesome-designs Build a stat dashboard row with 4 KPI cards
/awesome-designs Review the empty state in the workflow list page
```

## Loaded skill

When you run this command, Claude will load and follow all rules in:

`.claude/skills/awesome-designs.md`

This includes:
- Design principles (hierarchy, spacing, color palette, motion, a11y)
- Component patterns (buttons, cards, badges, modals, stat cards)
- Workflow builder canvas node styles
- Filament customization guidelines
- Code quality rules (dark mode, semantic HTML, Tailwind class ordering)

---

$ARGUMENTS
