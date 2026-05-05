# /awesome-designs

Apply the **Awesome Designs v2** skill — Luminous Glass design language with Three.js and glassmorphism.

## What this command does

Activates the project's full design system and applies it to whatever you specify. This is the v2 skill: it includes glassmorphism, animated Three.js backgrounds, glow effects, and a premium component library tuned to this project's stack.

**Stack:** Tailwind CSS 4 · Alpine.js · Livewire 3 · Filament 5.2 · Blade · Three.js

## Usage

```
/awesome-designs [target]
```

- **No argument** — review and upgrade the last file discussed
- **File path** — apply to a specific Blade / Livewire component
- **Description** — build a new component from scratch

## Examples

```
/awesome-designs resources/views/livewire/workflow-builder-canvas.blade.php
/awesome-designs Build a glassmorphic dashboard hero with Three.js particle background
/awesome-designs Add a run history timeline to the WorkflowRun view page
/awesome-designs Redesign the stat card row with glow effects
/awesome-designs Build the 3-panel builder layout with glass sidebars
```

## What the skill provides

`.claude/skills/awesome-designs.md` — loaded in full when this command runs.

| Section | Contents |
|---|---|
| Design language | Luminous Glass philosophy, color system, typography scale |
| CSS tokens | `@theme` block with glass variables and glow shadows |
| Glassmorphism system | 3 glass levels, glass card, glass navbar, glass sidebar, glass modal |
| Button system | Glow CTA, glass secondary, danger — all with shimmer hover effects |
| Status badges | All 5 workflow statuses with animated Running pulse dot |
| Glass stat card | Ambient glow blob, shimmer top line, trend indicator |
| Empty state | Glass icon container with glow |
| Workflow builder | 3-panel shell, glass sidebars, node cards by type, SVG connector gradients |
| Run timeline | Vertical connector, per-step status dots, error display |
| Three.js — Pattern 1 | Particle network (dashboard background) — `particle-network.js` |
| Three.js — Pattern 2 | CSS floating orbs (no install needed) |
| Three.js — Pattern 3 | Node orbit animation (builder canvas backdrop) — `node-orbit.js` |
| Animation utilities | Stagger entrance, skeleton loader |
| Filament patterns | Badge + icon columns, toggle helpers, variable interpolation hints |
| Code quality rules | 8 rules covering dark mode, glass-first, Three.js lifecycle, a11y |

## Three.js setup (one-time)

```sh
pnpm add three
```

Alpine.js (bundled by Filament) handles init/destroy lifecycle via `x-init` + `$cleanup`.

---

$ARGUMENTS
