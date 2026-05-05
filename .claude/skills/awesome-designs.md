# Awesome Designs Skill — v2

You are a principal UI/UX engineer with deep expertise in modern glass-morphic interfaces, WebGL animations, and premium design systems. When this skill is active, every piece of UI you produce must feel polished, alive, and visually stunning — not merely functional.

---

## Stack

| Layer | Technology |
|---|---|
| CSS framework | Tailwind CSS 4 |
| Interactivity | Alpine.js (bundled by Filament) |
| Server UI | Livewire 3 + Blade |
| Admin panel | Filament 5.2 — `AdminPanelProvider` |
| 3D / WebGL | Three.js (`pnpm add three`) |
| Icons | Heroicons via `<x-heroicon-*>` |
| Primary color | `Color::Amber` — set in `app/Providers/Filament/AdminPanelProvider.php` |

---

## Design Language

### Philosophy
Interfaces in this project follow **Luminous Glass** — a design language built on depth, translucency, and kinetic energy. Every surface has light behind it. Every interaction has weight.

Three layers of depth:
1. **Background** — animated, Three.js or CSS gradient mesh
2. **Glass surfaces** — frosted, semi-transparent panels
3. **Content** — sharp typography and iconography on top

### Color System

```
Amber  — primary brand: amber-400 … amber-600
Violet — accent/trigger nodes: violet-400 … violet-600
Sky    — action nodes / info: sky-400 … sky-600
Emerald— success / completed
Rose   — danger / failed
Blue   — running / in-progress
Slate  — pending / skipped / neutral
```

Dark mode is the **default aesthetic**. Light mode must also work. Always write both.

### Typography Scale
```
Display   text-4xl font-bold tracking-tight
Heading   text-2xl font-semibold
Subhead   text-lg   font-medium
Body      text-sm   text-gray-300 dark:text-gray-300
Caption   text-xs   text-gray-400
```

---

## CSS Design Tokens

Place in `resources/css/app.css` (Tailwind CSS 4 `@theme` block):

```css
@theme {
  --color-brand: theme(colors.amber.500);
  --color-brand-dark: theme(colors.amber.600);

  --glass-bg:          rgba(255, 255, 255, 0.06);
  --glass-bg-strong:   rgba(255, 255, 255, 0.12);
  --glass-border:      rgba(255, 255, 255, 0.12);
  --glass-border-strong: rgba(255, 255, 255, 0.24);
  --glass-shadow:      0 8px 32px rgba(0, 0, 0, 0.36);
  --glass-shadow-lg:   0 24px 64px rgba(0, 0, 0, 0.5);

  --glow-amber:  0 0 24px rgba(251, 191, 36, 0.35);
  --glow-violet: 0 0 24px rgba(139, 92, 246, 0.35);
  --glow-sky:    0 0 24px rgba(56, 189, 248, 0.35);
}
```

---

## Glassmorphism System

### Glass Levels

**Level 1 — Subtle glass** (cards, panels on light backgrounds)
```
bg-white/8 backdrop-blur-md border border-white/12 shadow-lg
```

**Level 2 — Standard glass** (sidebars, modals, elevated surfaces)
```
bg-white/10 backdrop-blur-xl border border-white/16 shadow-xl
```

**Level 3 — Ultra glass** (hero panels, featured cards — dark bg required)
```
bg-white/6 backdrop-blur-2xl border border-white/20 shadow-2xl
```

**Dark mode addendum** — on dark backgrounds add inner highlight:
```
before:absolute before:inset-0 before:rounded-[inherit]
before:bg-gradient-to-b before:from-white/8 before:to-transparent before:pointer-events-none
```

### Glass Card
```blade
<div class="relative overflow-hidden rounded-2xl bg-white/8 backdrop-blur-xl border border-white/12 shadow-xl p-6
            hover:bg-white/12 hover:border-white/20 hover:shadow-2xl
            transition-all duration-300 ease-out group">
    {{-- Inner shimmer highlight --}}
    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/8 via-transparent to-transparent pointer-events-none"></div>

    <div class="relative z-10">
        <h3 class="text-base font-semibold text-white">Card Title</h3>
        <p class="mt-1 text-sm text-gray-400">Supporting description text.</p>
    </div>
</div>
```

### Glass Navbar
```blade
<nav class="sticky top-0 z-40 w-full
            bg-gray-950/70 backdrop-blur-xl
            border-b border-white/8
            shadow-[0_1px_0_rgba(255,255,255,0.06)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        {{-- Logo, nav links, actions --}}
    </div>
</nav>
```

### Glass Sidebar (3-panel builder layout)
```blade
{{-- Left palette sidebar --}}
<aside class="w-64 shrink-0 h-full
              bg-gray-950/60 backdrop-blur-xl
              border-r border-white/8
              flex flex-col overflow-y-auto">
    <div class="p-4 border-b border-white/8">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500">Palette</h2>
    </div>
    <div class="flex-1 p-4 space-y-2">
        {{-- Draggable palette items --}}
    </div>
</aside>

{{-- Right config sidebar --}}
<aside class="w-80 shrink-0 h-full
              bg-gray-950/60 backdrop-blur-xl
              border-l border-white/8
              flex flex-col overflow-y-auto">
    <div class="p-4 border-b border-white/8">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500">Configure</h2>
    </div>
    <div class="flex-1 p-4">
        {{-- Filament form schema for selected step --}}
    </div>
</aside>
```

### Glass Modal
```blade
<div x-data="{ open: false }">
    <button @click="open = true" class="...">Open</button>

    <div
        x-show="open"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        {{-- Blurred backdrop --}}
        <div class="fixed inset-0 bg-gray-950/70 backdrop-blur-sm" @click="open = false"></div>

        {{-- Glass panel --}}
        <div
            class="relative z-10 w-full max-w-lg overflow-hidden
                   rounded-2xl bg-gray-900/80 backdrop-blur-2xl
                   border border-white/16 shadow-2xl"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        >
            {{-- Top shimmer line --}}
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

            <div class="p-6">
                <h2 class="text-lg font-semibold text-white">Modal Title</h2>
                <p class="mt-2 text-sm text-gray-400">Content here.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="open = false"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-gray-300
                               bg-white/8 hover:bg-white/12 border border-white/12
                               transition-all duration-200 active:scale-95">
                        Cancel
                    </button>
                    <button
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white
                               bg-amber-500 hover:bg-amber-400
                               shadow-[0_0_20px_rgba(251,191,36,0.3)]
                               hover:shadow-[0_0_32px_rgba(251,191,36,0.5)]
                               transition-all duration-200 active:scale-95">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## Button System

### Glow Button (Primary CTA)
```blade
<button class="group relative inline-flex items-center gap-2
               px-5 py-2.5 rounded-xl text-sm font-semibold text-white
               bg-gradient-to-r from-amber-500 to-amber-600
               shadow-[0_0_20px_rgba(251,191,36,0.3)]
               hover:shadow-[0_0_32px_rgba(251,191,36,0.55)]
               hover:from-amber-400 hover:to-amber-500
               active:scale-95
               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-950
               transition-all duration-200">
    <x-heroicon-o-plus class="size-4" />
    Add Workflow
    {{-- Shimmer sweep on hover --}}
    <span class="absolute inset-0 rounded-xl overflow-hidden">
        <span class="absolute inset-0 -translate-x-full group-hover:translate-x-full
                     bg-gradient-to-r from-transparent via-white/20 to-transparent
                     transition-transform duration-500 ease-in-out"></span>
    </span>
</button>
```

### Glass Button (Secondary)
```blade
<button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
               text-gray-300 bg-white/8 hover:bg-white/14
               border border-white/12 hover:border-white/20
               active:scale-95 focus:outline-none focus:ring-2 focus:ring-white/20
               transition-all duration-200">
    Cancel
</button>
```

### Danger Button
```blade
<button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
               text-white bg-rose-500/80 hover:bg-rose-500
               border border-rose-400/30
               shadow-[0_0_16px_rgba(244,63,94,0.2)] hover:shadow-[0_0_24px_rgba(244,63,94,0.4)]
               active:scale-95 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-gray-950
               transition-all duration-200">
    <x-heroicon-o-trash class="size-4" />
    Delete
</button>
```

---

## Status Badges

All five workflow statuses — `pending`, `running`, `completed`, `failed`, `skipped`:

```blade
{{-- Pending --}}
<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
             bg-slate-500/15 text-slate-400 ring-1 ring-inset ring-slate-500/25">
    <span class="size-1.5 rounded-full bg-slate-400"></span>
    Pending
</span>

{{-- Running — pulsing dot --}}
<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
             bg-blue-500/15 text-blue-400 ring-1 ring-inset ring-blue-500/25">
    <span class="size-1.5 rounded-full bg-blue-400 animate-pulse"></span>
    Running
</span>

{{-- Completed --}}
<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
             bg-emerald-500/15 text-emerald-400 ring-1 ring-inset ring-emerald-500/25">
    <x-heroicon-s-check-circle class="size-3" />
    Completed
</span>

{{-- Failed --}}
<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
             bg-rose-500/15 text-rose-400 ring-1 ring-inset ring-rose-500/25">
    <x-heroicon-s-x-circle class="size-3" />
    Failed
</span>

{{-- Skipped --}}
<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium
             bg-gray-500/15 text-gray-500 ring-1 ring-inset ring-gray-500/20">
    <x-heroicon-s-minus-circle class="size-3" />
    Skipped
</span>
```

Filament `TextColumn` mapping:
```php
TextColumn::make('status')
    ->badge()
    ->icon(fn($state) => match($state) {
        'pending'   => 'heroicon-s-clock',
        'running'   => 'heroicon-s-arrow-path',
        'completed' => 'heroicon-s-check-circle',
        'failed'    => 'heroicon-s-x-circle',
        'skipped'   => 'heroicon-s-minus-circle',
    })
    ->color(fn($state) => match($state) {
        'pending'   => 'gray',
        'running'   => 'info',
        'completed' => 'success',
        'failed'    => 'danger',
        'skipped'   => 'gray',
    }),
```

---

## Glass Stat Card (Dashboard)

```blade
<div class="relative overflow-hidden rounded-2xl p-6
            bg-gray-900/60 backdrop-blur-xl
            border border-white/10
            shadow-[0_8px_32px_rgba(0,0,0,0.4)]
            hover:border-white/20 hover:shadow-[0_16px_48px_rgba(0,0,0,0.5)]
            transition-all duration-300 group">

    {{-- Ambient glow blob --}}
    <div class="absolute -top-6 -right-6 size-24 rounded-full
                bg-amber-500/20 blur-2xl
                group-hover:bg-amber-500/30 transition-colors duration-300"></div>

    {{-- Top shimmer --}}
    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-amber-500/50 to-transparent"></div>

    <div class="relative z-10 flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Total Workflows</p>
            <p class="mt-3 text-4xl font-bold text-white tabular-nums">{{ $count }}</p>
            <p class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-400">
                <x-heroicon-s-arrow-trending-up class="size-3" />
                +12% this month
            </p>
        </div>
        <div class="rounded-xl p-2.5 bg-amber-500/15 border border-amber-500/25">
            <x-heroicon-o-bolt class="size-6 text-amber-400" />
        </div>
    </div>
</div>
```

---

## Empty State (Glass)

```blade
<div class="flex flex-col items-center justify-center py-20 text-center">
    {{-- Glass icon container --}}
    <div class="relative mb-6">
        <div class="size-20 rounded-2xl bg-white/6 backdrop-blur-xl border border-white/12
                    flex items-center justify-center shadow-xl">
            <x-heroicon-o-bolt class="size-9 text-amber-400" />
        </div>
        {{-- Glow --}}
        <div class="absolute inset-0 rounded-2xl bg-amber-500/10 blur-xl -z-10"></div>
    </div>

    <h3 class="text-lg font-semibold text-white">No workflows yet</h3>
    <p class="mt-2 text-sm text-gray-500 max-w-xs">
        Build your first automation by connecting triggers, conditions, and actions.
    </p>
    <div class="mt-8">
        {{-- Glow Button from above --}}
    </div>
</div>
```

---

## Workflow Builder Canvas

### 3-Panel Layout Shell
```blade
<div class="flex h-[calc(100vh-4rem)] overflow-hidden bg-gray-950">

    {{-- Three.js canvas sits behind everything --}}
    <canvas id="builder-bg" class="fixed inset-0 z-0 pointer-events-none opacity-40"></canvas>

    {{-- Left palette --}}
    <aside class="relative z-10 w-64 shrink-0 bg-gray-950/70 backdrop-blur-xl border-r border-white/8 flex flex-col">
        <div class="p-4 border-b border-white/8">
            <input type="search" placeholder="Search blocks…"
                   class="w-full rounded-lg bg-white/6 border border-white/10 px-3 py-2
                          text-sm text-gray-300 placeholder:text-gray-600
                          focus:outline-none focus:ring-1 focus:ring-amber-500/50
                          transition-all duration-200">
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-1" id="palette">
            {{-- Palette items rendered by Livewire --}}
        </div>
    </aside>

    {{-- Center canvas --}}
    <main class="relative z-10 flex-1 overflow-hidden" id="workflow-canvas"
          wire:ignore>
        {{-- Alpine drag-and-drop canvas rendered here --}}
    </main>

    {{-- Right config panel --}}
    <aside class="relative z-10 w-80 shrink-0 bg-gray-950/70 backdrop-blur-xl border-l border-white/8 flex flex-col">
        <div class="p-4 border-b border-white/8">
            <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500">Step Config</h2>
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            {{-- Dynamic Filament form for selected step --}}
        </div>
    </aside>
</div>
```

### Workflow Node Cards
```blade
{{-- Trigger node --}}
<div class="group relative w-60 rounded-xl overflow-hidden cursor-grab active:cursor-grabbing
            bg-gray-900/80 backdrop-blur-md
            border border-violet-500/30 hover:border-violet-400/60
            shadow-[0_0_0_1px_rgba(139,92,246,0.1),0_8px_24px_rgba(0,0,0,0.4)]
            hover:shadow-[0_0_16px_rgba(139,92,246,0.25),0_8px_32px_rgba(0,0,0,0.5)]
            transition-all duration-200
            data-[selected]:ring-2 data-[selected]:ring-violet-500 data-[selected]:ring-offset-2 data-[selected]:ring-offset-gray-950">
    {{-- Colored top bar --}}
    <div class="h-1 bg-gradient-to-r from-violet-500 to-violet-400"></div>
    <div class="p-4">
        <div class="flex items-center gap-2 mb-2">
            <div class="rounded-lg p-1.5 bg-violet-500/15 border border-violet-500/25">
                <x-heroicon-o-bolt class="size-4 text-violet-400" />
            </div>
            <span class="text-xs font-semibold uppercase tracking-wider text-violet-400">Trigger</span>
        </div>
        <p class="text-sm font-medium text-white">{{ $node['label'] }}</p>
        <p class="mt-0.5 text-xs text-gray-500 truncate">{{ $node['description'] ?? '' }}</p>
    </div>
</div>

{{-- Action node --}}
{{-- border-sky-500/30, glow sky, top bar from-sky-500 to-sky-400, icon bg-sky-500/15 --}}

{{-- Condition node --}}
{{-- border-amber-500/30, glow amber, top bar from-amber-500 to-amber-400, icon bg-amber-500/15 --}}
```

### SVG Connectors
```blade
<svg class="absolute inset-0 w-full h-full pointer-events-none z-0" overflow="visible">
    {{-- Bezier path between nodes --}}
    <path
        d="M {{ $x1 }},{{ $y1 }} C {{ $cx1 }},{{ $y1 }} {{ $cx2 }},{{ $y2 }} {{ $x2 }},{{ $y2 }}"
        fill="none"
        stroke="url(#connector-gradient)"
        stroke-width="2"
        stroke-linecap="round"
    />
    {{-- Arrow head --}}
    <circle cx="{{ $x2 }}" cy="{{ $y2 }}" r="4" fill="rgb(251,191,36)" />

    <defs>
        <linearGradient id="connector-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="rgba(139,92,246,0.6)" />
            <stop offset="100%" stop-color="rgba(56,189,248,0.6)" />
        </linearGradient>
    </defs>
</svg>
```

---

## Run History Timeline

```blade
<div class="space-y-0">
    @foreach($steps as $i => $step)
    <div class="relative flex gap-4 pb-8 last:pb-0">
        {{-- Vertical connector line --}}
        @if(!$loop->last)
        <div class="absolute left-4 top-8 bottom-0 w-px bg-gradient-to-b from-white/15 to-transparent"></div>
        @endif

        {{-- Status dot --}}
        <div class="relative z-10 shrink-0 size-8 rounded-full flex items-center justify-center
                    @if($step->status === 'completed') bg-emerald-500/20 border border-emerald-500/40
                    @elseif($step->status === 'failed')  bg-rose-500/20   border border-rose-500/40
                    @elseif($step->status === 'running') bg-blue-500/20   border border-blue-500/40 animate-pulse
                    @else                                bg-gray-800      border border-white/10 @endif">
            {{-- Icon based on status --}}
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0 pt-0.5">
            <div class="rounded-xl bg-white/4 border border-white/8 p-4 hover:bg-white/6 transition-colors duration-200">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium text-white truncate">{{ $step->action_type }}</p>
                    <span class="text-xs text-gray-600 shrink-0 tabular-nums">
                        {{ $step->duration_ms }}ms
                    </span>
                </div>
                @if($step->error)
                <p class="mt-2 text-xs text-rose-400 font-mono bg-rose-500/8 rounded-lg px-3 py-2 border border-rose-500/15">
                    {{ $step->error }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
```

---

## Three.js Integration

### Setup — install once
```sh
pnpm add three
```

### Pattern 1 — Animated Particle Network (Dashboard Background)

Create `resources/js/three/particle-network.js`:

```js
import * as THREE from 'three'

export function initParticleNetwork(canvas) {
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true })
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2))

    const scene = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(60, 1, 0.1, 1000)
    camera.position.z = 80

    // Particles
    const count = 180
    const positions = new Float32Array(count * 3)
    const velocities = []
    for (let i = 0; i < count; i++) {
        positions[i * 3]     = (Math.random() - 0.5) * 160
        positions[i * 3 + 1] = (Math.random() - 0.5) * 100
        positions[i * 3 + 2] = (Math.random() - 0.5) * 60
        velocities.push({
            x: (Math.random() - 0.5) * 0.04,
            y: (Math.random() - 0.5) * 0.04,
        })
    }

    const geo = new THREE.BufferGeometry()
    geo.setAttribute('position', new THREE.BufferAttribute(positions, 3))

    const mat = new THREE.PointsMaterial({
        color: 0xf59e0b,   // amber-400
        size: 0.6,
        transparent: true,
        opacity: 0.7,
        sizeAttenuation: true,
    })

    const points = new THREE.Points(geo, mat)
    scene.add(points)

    // Connection lines
    const lineMat = new THREE.LineBasicMaterial({ color: 0xfbbf24, transparent: true, opacity: 0.12 })
    const lineGeo = new THREE.BufferGeometry()
    const maxLines = 300
    const linePositions = new Float32Array(maxLines * 6)
    lineGeo.setAttribute('position', new THREE.BufferAttribute(linePositions, 3))
    lineGeo.setDrawRange(0, 0)
    const lines = new THREE.LineSegments(lineGeo, lineMat)
    scene.add(lines)

    const resize = () => {
        const { clientWidth: w, clientHeight: h } = canvas.parentElement
        renderer.setSize(w, h, false)
        camera.aspect = w / h
        camera.updateProjectionMatrix()
    }
    resize()
    const ro = new ResizeObserver(resize)
    ro.observe(canvas.parentElement)

    let frame
    const pos = geo.attributes.position

    const animate = () => {
        frame = requestAnimationFrame(animate)
        for (let i = 0; i < count; i++) {
            pos.array[i * 3]     += velocities[i].x
            pos.array[i * 3 + 1] += velocities[i].y
            // Wrap
            if (pos.array[i * 3]     >  80) pos.array[i * 3]     = -80
            if (pos.array[i * 3]     < -80) pos.array[i * 3]     =  80
            if (pos.array[i * 3 + 1] >  50) pos.array[i * 3 + 1] = -50
            if (pos.array[i * 3 + 1] < -50) pos.array[i * 3 + 1] =  50
        }
        pos.needsUpdate = true

        // Rebuild connection lines for nearby particles
        let lineIdx = 0
        const lp = lineGeo.attributes.position.array
        for (let a = 0; a < count && lineIdx < maxLines; a++) {
            for (let b = a + 1; b < count && lineIdx < maxLines; b++) {
                const dx = pos.array[a*3]   - pos.array[b*3]
                const dy = pos.array[a*3+1] - pos.array[b*3+1]
                if (dx*dx + dy*dy < 400) {
                    lp[lineIdx*6]   = pos.array[a*3];   lp[lineIdx*6+1] = pos.array[a*3+1]; lp[lineIdx*6+2] = 0
                    lp[lineIdx*6+3] = pos.array[b*3];   lp[lineIdx*6+4] = pos.array[b*3+1]; lp[lineIdx*6+5] = 0
                    lineIdx++
                }
            }
        }
        lineGeo.attributes.position.needsUpdate = true
        lineGeo.setDrawRange(0, lineIdx * 2)

        renderer.render(scene, camera)
    }
    animate()

    return () => {
        cancelAnimationFrame(frame)
        ro.disconnect()
        renderer.dispose()
    }
}
```

Mount via Alpine.js in the Blade layout:
```blade
<div class="relative min-h-screen bg-gray-950"
     x-data
     x-init="
         const { initParticleNetwork } = await import('/resources/js/three/particle-network.js')
         const destroy = initParticleNetwork($refs.bgCanvas)
         $cleanup(destroy)
     ">
    <canvas x-ref="bgCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0 opacity-50"></canvas>
    <div class="relative z-10">
        {{ $slot }}
    </div>
</div>
```

---

### Pattern 2 — Floating Orb Background (Lighter alternative, no install needed)

Pure CSS — use when Three.js is too heavy for the context:

```blade
<div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
    {{-- Orb 1 - amber --}}
    <div class="absolute -top-40 -left-40 size-[600px] rounded-full
                bg-amber-500/20 blur-[120px]
                animate-[pulse_8s_ease-in-out_infinite]"></div>
    {{-- Orb 2 - violet --}}
    <div class="absolute top-1/2 -right-40 size-[500px] rounded-full
                bg-violet-500/15 blur-[100px]
                animate-[pulse_10s_ease-in-out_infinite_2s]"></div>
    {{-- Orb 3 - sky --}}
    <div class="absolute -bottom-40 left-1/3 size-[400px] rounded-full
                bg-sky-500/10 blur-[80px]
                animate-[pulse_12s_ease-in-out_infinite_4s]"></div>
</div>
```

---

### Pattern 3 — Three.js Workflow Node Orbit (Builder Canvas Backdrop)

Create `resources/js/three/node-orbit.js`:

```js
import * as THREE from 'three'

export function initNodeOrbit(canvas) {
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true })
    renderer.setPixelRatio(Math.min(devicePixelRatio, 2))

    const scene = new THREE.Scene()
    const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 500)
    camera.position.set(0, 0, 120)

    // Ring of orbiting nodes
    const nodeColors = [0xf59e0b, 0x8b5cf6, 0x38bdf8]
    const orbitGroups = nodeColors.map((color, i) => {
        const group = new THREE.Group()
        group.rotation.z = (i / nodeColors.length) * Math.PI * 2

        const sphere = new THREE.Mesh(
            new THREE.SphereGeometry(1.5, 16, 16),
            new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.8 })
        )
        sphere.position.set(40 + i * 12, 0, 0)
        group.add(sphere)

        // Glow ring around sphere
        const ring = new THREE.Mesh(
            new THREE.RingGeometry(2.5, 3.5, 32),
            new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.3, side: THREE.DoubleSide })
        )
        ring.position.copy(sphere.position)
        group.add(ring)

        scene.add(group)
        return group
    })

    const resize = () => {
        const w = canvas.parentElement.clientWidth
        const h = canvas.parentElement.clientHeight
        renderer.setSize(w, h, false)
        camera.aspect = w / h
        camera.updateProjectionMatrix()
    }
    resize()
    const ro = new ResizeObserver(resize)
    ro.observe(canvas.parentElement)

    let frame
    const animate = () => {
        frame = requestAnimationFrame(animate)
        orbitGroups.forEach((g, i) => {
            g.rotation.z += 0.002 * (i % 2 === 0 ? 1 : -1)
        })
        renderer.render(scene, camera)
    }
    animate()

    return () => { cancelAnimationFrame(frame); ro.disconnect(); renderer.dispose() }
}
```

---

## Animation Utilities

### Stagger entrance (list items)
```blade
@foreach($items as $i => $item)
<div class="opacity-0 translate-y-4"
     x-init="
         setTimeout(() => {
             $el.classList.add('transition-all', 'duration-500', 'ease-out')
             $el.classList.remove('opacity-0', 'translate-y-4')
         }, {{ $i * 60 }})
     ">
    {{-- item content --}}
</div>
@endforeach
```

### Skeleton loader
```blade
<div class="animate-pulse space-y-3">
    <div class="h-4 bg-white/8 rounded-lg w-3/4"></div>
    <div class="h-4 bg-white/8 rounded-lg w-1/2"></div>
    <div class="h-10 bg-white/8 rounded-xl w-full mt-4"></div>
</div>
```

---

## Filament Customizations

All changes go in `app/Providers/Filament/AdminPanelProvider.php`:

```php
->colors(['primary' => Color::Amber])   // current default — keep or change to Violet for triggers
```

Table/form patterns:
```php
// Status column with icon + badge
TextColumn::make('status')
    ->badge()
    ->icon(fn($s) => match($s) {
        'pending'   => 'heroicon-s-clock',
        'running'   => 'heroicon-s-arrow-path',
        'completed' => 'heroicon-s-check-circle',
        'failed'    => 'heroicon-s-x-circle',
        'skipped'   => 'heroicon-s-minus-circle',
    })
    ->color(fn($s) => match($s) {
        'pending'   => 'gray',
        'running'   => 'info',
        'completed' => 'success',
        'failed'    => 'danger',
        'skipped'   => 'gray',
    })

// Toggle with description
Toggle::make('is_active')
    ->label('Active')
    ->helperText('Paused workflows will not respond to triggers.')

// Variable interpolation hint on text inputs
TextInput::make('body')
    ->helperText('Supports {{trigger.field}}, {{step.id.output.key}}, {{secret.name}}, {{now}}')
```

---

## Code Quality Rules

1. **Dark mode always** — every class needs a `dark:` variant or is dark-only by design
2. **No inline `style=""`** — use Tailwind utilities; for dynamic values use CSS custom properties via `style="--val: {{ $x }}"` then consume with `[--val]` utilities
3. **Glass before opaque** — prefer `bg-white/10 backdrop-blur-xl` over solid `bg-gray-900` for surfaces
4. **Three.js lifecycle** — always return a destroy function; always call `renderer.dispose()` and `cancelAnimationFrame()` on cleanup via Alpine `$cleanup`
5. **Class ordering** — position → display/layout → spacing → sizing → typography → color/glass → border → shadow/glow → interactivity → transition → animation
6. **Extract to Blade components** — once a pattern appears twice, make `<x-glass-card>`, `<x-status-badge>`, `<x-glow-button>`
7. **Semantic HTML** — `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>`, `<time>`
8. **a11y** — focus rings on every interactive element using `focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-950`; pulsing "Running" dots must have `aria-label="Running"`
