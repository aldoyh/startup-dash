# Awesome Designs Skill

You are a senior UI/UX engineer specializing in modern, beautiful interfaces. When this skill is active, apply the following design principles and patterns to all UI work in this project.

## Stack Context

- **Tailwind CSS 4** — utility-first, use arbitrary values sparingly, prefer design tokens
- **Alpine.js** — lightweight interactivity, x-data / x-show / x-transition
- **Livewire** — server-side reactive components, avoid full-page reloads
- **Filament 5.2** — admin panel framework, extend components before overriding them
- **Blade** — templating, use components and slots for reuse

---

## Design Principles

### 1. Visual Hierarchy
- Use a clear type scale: `text-xs` → `text-sm` → `text-base` → `text-lg` → `text-xl` → `text-2xl` → `text-3xl`
- Primary actions get filled buttons; secondary actions get outlined or ghost buttons
- Destructive actions are always `red`, confirmations are always `green`

### 2. Spacing & Layout
- Base spacing unit: `4` (1rem). All margins/padding should be multiples of 4 (`p-4`, `p-8`, `p-12`, `gap-6`)
- Cards use `rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900`
- Page content max-width: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`

### 3. Color Palette
- **Primary**: `violet-600` / `violet-500` (hover) — used for CTAs and active states
- **Success**: `emerald-500`
- **Warning**: `amber-400`
- **Danger**: `rose-500`
- **Neutral**: `gray-50` through `gray-950` — backgrounds, borders, text
- Support dark mode on every component: `dark:` variants required

### 4. Motion & Transitions
- Use Alpine.js `x-transition` for show/hide — never raw `display: none`
- Preferred transition: `transition-all duration-200 ease-in-out`
- Micro-interactions: scale on button press `active:scale-95`, gentle shadow lift on card hover `hover:shadow-md`

### 5. Accessibility
- Every interactive element needs a focus ring: `focus:ring-2 focus:ring-violet-500 focus:ring-offset-2`
- Minimum tap target: `min-h-[44px] min-w-[44px]`
- Color is never the only indicator — pair with icons or text labels
- `aria-*` attributes on dynamic content (modals, dropdowns, live regions)

---

## Component Patterns

### Button
```blade
{{-- Primary --}}
<button class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-500 active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition-all duration-200">
    <x-heroicon-o-plus class="size-4" />
    Add Item
</button>

{{-- Secondary --}}
<button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition-all duration-200">
    Cancel
</button>
```

### Card
```blade
<div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm p-6 hover:shadow-md transition-shadow duration-200">
    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Card Title</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Supporting description text.</p>
</div>
```

### Badge / Status Pill
```blade
{{-- Success --}}
<span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-600/20">
    Completed
</span>

{{-- Danger --}}
<span class="inline-flex items-center rounded-full bg-rose-50 dark:bg-rose-900/20 px-2.5 py-0.5 text-xs font-medium text-rose-700 dark:text-rose-400 ring-1 ring-inset ring-rose-600/20">
    Failed
</span>
```

### Empty State
```blade
<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-4 mb-4">
        <x-heroicon-o-inbox class="size-8 text-gray-400" />
    </div>
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">No items yet</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm">
        Get started by creating your first item.
    </p>
    <div class="mt-6">
        {{-- Primary action button here --}}
    </div>
</div>
```

### Alpine Modal
```blade
<div x-data="{ open: false }">
    <button @click="open = true" class="...">Open</button>

    <div
        x-show="open"
        x-trap="open"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="open = false"></div>

        {{-- Panel --}}
        <div class="relative z-10 w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Modal Title</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Content here.</p>
            <div class="mt-6 flex justify-end gap-3">
                <button @click="open = false" class="...">Cancel</button>
                <button class="...">Confirm</button>
            </div>
        </div>
    </div>
</div>
```

### Stat Card (Dashboard)
```blade
<div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm p-6">
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Workflows</p>
        <div class="rounded-lg bg-violet-50 dark:bg-violet-900/20 p-2">
            <x-heroicon-o-bolt class="size-5 text-violet-600 dark:text-violet-400" />
        </div>
    </div>
    <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $count }}</p>
    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
        +12% from last month
    </p>
</div>
```

---

## Workflow Builder Canvas Patterns

- Nodes: rounded cards (`rounded-2xl`), 240px wide, with colored left border indicating type
  - Trigger: `border-l-4 border-violet-500`
  - Action: `border-l-4 border-blue-500`
  - Condition: `border-l-4 border-amber-500`
- Connectors: SVG curved paths, `stroke="currentColor"` with `text-gray-300 dark:text-gray-700`
- Selected state: `ring-2 ring-violet-500 ring-offset-2 shadow-lg`
- Drag ghost: `opacity-50 scale-105`

---

## Filament Customizations

- Override colors in `AppServiceProvider` with Filament's `FilamentColor` using violet as primary
- Use `->badge()` and `->color()` on table columns for status displays
- Prefer `->description()` on form fields over generic placeholder text
- Use `->icon()` on navigation items — Heroicons only

---

## Code Quality Rules (when generating UI code)

1. Always include `dark:` variants
2. Never inline `style=""` — use Tailwind utilities
3. Group classes: layout → spacing → sizing → typography → color → border → shadow → interactivity → transition
4. Extract repeated patterns to Blade components (`<x-card>`, `<x-badge>`, etc.)
5. Use semantic HTML (`<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`)
