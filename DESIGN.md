# Coolify UI design system

This document defines Coolify's UI design system for its Livewire + Blade +
Alpine + Tailwind v4 frontend. The visual system covers the global shell,
project and environment pages, application navigation, settings surfaces,
tables, modals, toasts, terminals, and metrics.

Use this file as the source of truth for frontend design work. Update it in the
same change whenever a new shared visual pattern or component is introduced.

Onboarding validation and live server validation checkpoints share
`<x-checkpoint-item>` (idle / pending / running / success / error) inside a
compact divided list, not legacy green check SVGs or fixed-width status rows.

> **Maintainer rules**
>
> - Keep the work frontend-focused unless existing data must be exposed to the
>   view.
> - Preserve routes, Livewire bindings, permissions, confirmations, and working
>   interactions while changing layout and presentation.
> - Add or update tests when a UI change affects behavior. Follow the testing
>   requirements in `AGENTS.md`.
> - Validate Blade with `./scripts/dev exec php artisan view:cache`, then clear
>   it with `./scripts/dev exec php artisan view:clear`.
> - Build frontend assets with `npm run build`.
> - Use existing components before adding another styling abstraction.

---

## 1. Visual direction

This fork uses a **dark interface with a neutral visual hierarchy**. Surface
depth and action emphasis use brightness; health, warnings and errors retain
distinct semantic colors:

- near-black canvas (`#0a0a0a`), `#f5f5f7` foreground, `#8e8e93` muted copy,
  and distinct surface steps (`#101012`, `#161618`, `#202023`, `#242427`);
- **rings instead of borders and shadows**: containers are outlined by an
  inset `ring-hairline` ring (white/18); selection is a
  brighter ring (`ring-white/30`), not a color;
- **interactive = pill** (`rounded-full`: buttons, nav links, tabs, badges,
  chips); **container = 18–24px** (cards 18px, popovers 14px, modals and the
  command palette 22px); small technical labels and code wells use 8px;
- **importance = fill, not hue**: the one primary action of a surface is a
  solid white pill with black text; secondary actions are outline pills;
  warnings use amber, independently of neutral active tabs and primary actions;
- Inter for every UI string, Geist Mono for machine values (hashes, domains,
  ports, durations, code), `tabular-nums` wherever numbers change;
- decoration is limited to soft white radial glows, a dot grid, and gradient
  hairlines.

Semantic hue remains visible in these places:

1. the operational / success dot (`--color-success` `#4ade80`);
2. destructive and error states (`--color-error` `#ff453a`);
3. warning states (`--color-warning` `#fbbf24`), including unhealthy and
   restarting containers, missing health checks, callouts and toasts;
4. syntax highlighting (GitHub Dark palette, see §3).

Legacy Coollabs utilities retain their purple brand meaning. Use `accent`/`fg`
for new neutral emphasis, and `warning` only for attention or health states.

Other rules:

- sentence-case labels and headings;
- outline Reicon glyphs through `<x-reicon>`, 14–16px;
- full-width data tables for dense collections;
- never use the em dash (`—`) in UI copy. Prefer a period, colon, comma, or
  ASCII hyphen (`-`) for empty cells and separators.

Avoid light surfaces, brand-colored fills, drop shadows on cards, thick
dividers, native browser selects, and isolated colored buttons.

Buttons are flat: no depth edge, no lift. Hover changes only the fill
(`hover:bg-white/[0.06]` on outline pills, `hover:bg-white/90` on the white
pill); pressing scales to 0.97. Focus-visible shows a soft white halo
(`0 0 0 3px rgb(255 255 255 / 0.12)`) with a `--color-ring` edge. Destructive
buttons are red text on a red/35 outline and fill with red/10 on hover, never a
solid red block.

---

## 2. Development and cascade notes

PHP runs in this branch's Coolify container (`./scripts/dev exec …`). The main
checkout serves the app at `http://localhost:8000` with Vite on `5173`;
worktrees use the port block printed by `./scripts/dev urls`.

`resources/css/app.css` still contains unlayered global element rules for
headings, labels, and tables. Tailwind utilities are layered, so the
unlayered rules can win unexpectedly.

The settings and dense-surface CSS therefore lives as plain unlayered CSS near
the end of `resources/css/app.css`, beginning at:

```css
/* Coollabs layer-card settings surfaces */
```

Important consequences:

- scope settings forms with `.application-settings-form` or
  `.application-settings-workspace`;
- add shared surface overrides to the unlayered block instead of stacking
  `!important` utilities;
- listbox panels require ancestors with `overflow: visible`;
- anchored cards use `scroll-margin-top: 7rem` to clear the fixed topbar and
  the page's top padding;
- modal shells reuse the layer-card classes but keep content-width sizing on
  desktop;
- Alpine code inside quoted Blade attributes must not introduce conflicting
  quote characters.

---

## 3. Tokens and color behavior

The product is **dark-only**. `layouts/base.blade.php` renders
`<html class="dark" data-theme="dark">`, re-asserts it after every
`wire:navigate`, without deleting saved `theme`, `themeColor` or `customMode`
preferences. There is no theme switcher and no light or custom palette; the
Appearance page only offers the page-width preference. Existing `dark:` variants are retained for upstream compatibility. New shared
components should use semantic tokens instead of adding light-only branches.
Restoring the light/system/custom theme picker requires verifying those modes
across the full interface; this fork currently applies the dark palette.

All tokens live in the `@theme` block and the `:root, .dark` surface block of
`resources/css/app.css`.

### Semantic colors

| Token | Value | Use |
|---|---|---|
| `--color-app` / `--color-panel` | `#0a0a0a` / `#101012` | page canvas, sidebar, topbar |
| `--color-surface` | `#161618` | card shells, code windows, editors |
| `--color-raised` | `#202023` | raised nodes |
| `--color-selected` / `--color-popover` | `#2c2c2e` / `#242427` | menus, listbox panels, tooltips |
| `--color-fg` | `#f5f5f7` | primary text, primary fill |
| `--color-fg-dim` | `#aeaeb2` | body text |
| `--color-fg-faint` | `#8e8e93` | muted copy, labels, descriptions |
| `--color-accent` | `#f5f5f7` | focus, active, primary (with `--color-accent-foreground` `#000`) |
| `--color-control` | `#85858b` | enabled form boundaries (at least 3:1) |
| `--color-ring` | `#f5f5f7` | focus edge |
| `--color-warning` | `#fbbf24` | attention and health warnings |
| `--color-success` | `#4ade80` | operational dot and success icons only |
| `--color-error` | `#ff453a` | destructive and errors |
| `--color-chart-1…5` | `#f5f5f7` `#a1a1a6` `#6e6e73` `#48484a` `#2c2c2e` | chart series by brightness |

Semantic tokens retain their names: `warning` is amber, `success` is green,
`error` is red, and the legacy `coollabs` scale is purple. Neutral primary
actions use `bg-accent text-accent-foreground`; active items use `fg` and
neutral surface fills. Do not use `warning` as a neutral brand accent.

### Surface tokens

| Token | Value | Use |
|---|---|---|
| `--coollabs-canvas` | `#0a0a0a` | page canvas |
| `--coollabs-elevated` / `--coollabs-base` | `#161618` | card shell and body (one plane) |
| `--coollabs-recessed` | `#101012` | inputs, listbox triggers, code wells |
| `--coollabs-fill` | `#1c1c1e` | passive fills, dividers in dense rows |
| `--coollabs-line` | `--color-control` (`#85858b`) | control outlines |
| `--coollabs-hairline` | `white/18` | container rings |
| `--coollabs-subtle` | `#8e8e93` | labels and descriptions |

### White opacity scale

| Opacity | Role |
|---|---|
| `white/[0.02]`–`[0.025]` | subtle row hover; cards use `bg-surface` |
| `white/[0.04]`–`[0.05]` | chips, ghost hover |
| `white/[0.06]` | inner dividers (`border-t` inside a card), menu hover, inline code |
| `white/18` | decorative tile and card rings (`ring-hairline`) |
| `white/[0.08]` | header/sidebar borders, nav active fill, status chip ring |
| `white/10` | popover and modal decoration; inputs use `border-control` |
| `white/15` | outline pills and outline badges |
| `warning/40` | warning callout ring |
| `white/30` | selected card ring |
| `white/60` | bottom stop of title gradients |

### Shadows

Cards use rings for depth. Modals and floating menus use explicit
`shadow-window` and `shadow-dropdown` tokens. Tailwind utility shadows keep
their standard definitions so existing components and packages retain their
depth. Glass is reserved for topbars, modals, the command palette and the
unsaved pill.

Enabled form boundaries must contrast at least 3:1 against both their fill and
the surrounding surface. Focus and error borders use opaque semantic colors.
Verify rendered colors in `resources/js/interface-accessibility.test.js`, not
only class strings. Decorative card dividers can remain softer.

### Syntax highlighting (GitHub Dark)

Monaco (`coolify-dark` theme) and the terminal ANSI palette use:
keywords `#ff7b72`, strings `#a5d6ff`, numbers/constants `#79c0ff`, types and
variables `#ffa657`, functions `#d2a8ff`, keys and tags `#7ee787`, comments
`#7d8590`, plain code `#e8e8ea`, punctuation `#8e8e93`, line numbers `white/20`.
Code surfaces are `#0a0a0a` with an inset `white/9` ring, Geist Mono 12.5px at a
22px line height.

### Charts

Charts are monochrome: series are separated by brightness using the
`--chart-*` scale (`cpuColor` is `#f5f5f7`, `ramColor`
`#8e8e93`, `textColor` `#8e8e93`). HTTP status breakdowns use `2xx` white,
`3xx` `#a1a1a6`, `4xx` `#6e6e73` and keep `5xx` destructive red; the geo map is
a five-step gray ramp.

---

## 4. Typography, decoration, and motion

| Role | Size / line height | Weight | Tracking | Color |
|---|---|---|---|---|
| Page title (`h1`) | 28px / 1.1 | 600 | −0.03em | `white → white/60` gradient |
| Auth / error display | 38px / 1.06 | 600 | −0.035em | gradient |
| Section heading | 15px / 1.5 | 600 | −0.01em | `fg` |
| Card title | 14px / 1.5 | 500 | 0 | `fg` |
| Lead / page summary | 14–15px / 1.625 | 400 | 0 | `fg-faint`, `max-w-2xl` |
| Body | 13–14px | 400 | 0 | `fg-dim` |
| Nav link | 13px | 400 | 0 | `fg-faint` → `fg` |
| Overline (table headers, nav sections, group labels) | 10.5–11px | 400 | +0.025em, uppercase | `fg-faint` |
| Kbd | 10px | 400 | 0 | `fg-faint`, `ring-white/10`, 4px radius |
| Mono values | 11–13px | 400–600 | 0 | Geist Mono |

Text-only `h1` elements get the gradient automatically (`h1:not(:has(*))`);
apply `.text-gradient` elsewhere. Titles that contain badges or icons keep a
flat `fg` so `currentColor` glyphs stay visible.

### Decorative layers

- `.app-canvas` (the `<main>` content area and the boarding layout) paints a
  top radial glow `radial-gradient(60% 50% at 50% 0%, white/6, transparent)`;
- auth and error shells add the same glow plus a blurred `white/3` spot;
- `.dot-grid` (22px, `white/18` dots) and the masked grid behind `x-empty`;
- `.hairline`: a 1px `transparent → white/10 → transparent` divider.

### Motion

- Default duration 150ms; `--ease-out` `cubic-bezier(0, 0, .2, 1)`,
  `--ease-in-out` `cubic-bezier(.4, 0, .2, 1)`, `--ease-out-fluid`
  `cubic-bezier(.32, .72, 0, 1)` for drawers and sheets.
- Root and nested scrolling stay native. Explicit settings jumps and
  suggestion navigation call `scrollElementIntoView()` from
  `resources/js/smooth-scroll.js`; it uses the browser API and moves instantly
  when `prefers-reduced-motion: reduce` is active. Browser/Livewire navigation
  controls history restoration without a second scroll engine.

---

## 5. Page shells and navigation

### Global shell

- The desktop topbar is 48px of glass: `border-b border-white/[0.08]
  bg-black/70 backdrop-blur-2xl backdrop-saturate-150`. The brand is the
  monochrome logo (`/coolify-logo-monochrome.svg` with `invert`) plus
  "Coolify" in `text-fg/90`, fading to `opacity-70` on hover; the version is
  Geist Mono 10.5px muted. The mobile topbar uses the same glass and ringed
  `size-7` pill buttons.
- The sidebar is black with a `white/[0.08]` right border. Its search trigger
  is a 10px-radius ringed field with a `⌘K` kbd (10px, `ring-white/10`, 4px
  radius), matching the Coolify header search.
- Nav rows are 32px pills (`rounded-full`), 13px regular text in `fg-faint`
  that brightens to `fg` on hover. The active row is `bg-white/[0.08]
  text-fg`; there is no accent rail and no colored icon. Section labels are
  10.5px uppercase overlines with +0.025em tracking.
- Nested items sit behind a thin 1px guide line (`.nav-children`) and use the
  same selected pill, not a thick box border.
- The update badge sits on the version row and uses a tiny white pill.

### Resource navigation

Application, service, database, and server pages have no second navigation
bar; content starts directly below the 48px global topbar. Every route in a
resource family (settings pages, backups, logs, terminal, metrics, danger zone)
is an entry in its grouped settings sidebar. Do not add a tab row, a large
in-flow resource heading, or legacy `.navbar-main` tabs to one resource type.

Keep route-derived active state in Blade/Livewire. Do not rely only on Alpine
state because it can disappear after polling or a Livewire morph.

The global topbar owns the current resource identity, its compact status
badges, configuration warnings (`#configuration-warning-hud-slot`), and the
resource actions (`#resource-action-hud-slot`). If a resource is missing from
`x-top-breadcrumb`, extend the global topbar instead of repeating its name or
status summary in the page. Below `xl` the resource heading repeats the name,
status, and links in-flow because the desktop HUD is hidden there.

Desktop resource actions dock in `#resource-action-hud-slot` (visible from
`xl`) as one `<x-split-action>`. The main button is the primary action for the
current state (Deploy, Restart, Start, Restart Proxy); the caret opens a menu
with the secondary actions: Deploy (without cache) and Restart on applications,
Pull latest and restart / Force Restart / Force Deploy / Force Cleanup
Containers on services, Refresh Proxy Status on servers. Stop is the last menu
item in the error color. Stop, restart, and removal items open the existing
confirmation modals. Do not add a separate Advanced dropdown or collapse actions
into an overflow menu. Place Links (`x-applications.links`, `x-services.links`)
or the server Traefik Dashboard link immediately before the split action; Links
stay a separate dropdown because the URL list is unbounded. Below `xl` the same
split action renders full width under the in-flow resource name. A resource
that cannot deploy yet shows a single Actions dropdown that explains why.

The only fixed tab strip left is `x-dashboard.navbar`, and it renders only when
a page has at least two real sibling routes or header actions. Never repeat
main-sidebar destinations such as Dashboard, Projects, Terminal, Servers,
Sources, Destinations, or Storage as a tab row. A single collection page does
not need a tab just to fill the bar; keep its primary action in the page header.

A tab must be active on the page that renders it. A bar whose only tab
points at a different route reads as broken navigation, so project and
environment pages (`project.show`, `project.edit`, `project.environment.edit`,
`project.clone-me`) carry a plain page header with a 28px gradient title and a
14px muted summary instead of a bar. The environment identity and the way back to
its resources already live in `x-top-breadcrumb`; do not restate them in a
sub-header.

The dashboard is a compact overview, not a metrics wall. It has no page header:
live active deployments come first as a compact table, followed by traffic
analytics when a server has it enabled, then two full-width sections (Projects,
then Servers) that follow the projects-page grid pattern. Each section uses
`x-section-heading` linking to its full index. Communicate server health with
the shared status badge.

### Top-level dashboard destinations

Every page opened directly from the main sidebar uses the same compact content
shell:

- 28px gradient page title (`text-[28px]! leading-[1.1]! tracking-[-0.03em]!`)
  and a 14px muted summary (`mt-2 max-w-2xl text-[14px] leading-relaxed
  text-fg-faint`);
- the single primary action at the top right as the white pill;
- no legacy `coolbox`, `.navbar-main`, or oversized subtitle block;
- four-column compact cards for small browsable collections;
- a dense table instead of cards when the collection is expected to grow;
- `x-empty` anatomy for empty states;
- `x-status-badge` for state and `x-reicon` for all interface icons.

Collection cards are Coolify tiles: `rounded-2xl bg-surface p-4 ring-1
ring-inset ring-hairline`, brightening to `bg-raised
ring-control` on hover (no lift, no shadow). They are `min-h-28` or
`min-h-32`, use a 32px icon tile on `white/[0.06–0.08]`, and keep secondary
metadata at 11px. They must not grow into dashboard-sized summary cards. Sources, destinations, S3 storage, private keys, and shared-variable
scopes use this pattern.

Top-level settings families such as Team, Notifications, Keys & Tokens, and
instance Settings use their `*settings-layout` component: the same grouped,
icon-led settings sidebar as resources, plus a `settings-mobile-header` title
below `xl`. Do not nest `<button>` elements inside sidebar links.

### Route-family consistency

Treat every route family as one cohesive experience rather than styling only
its index or most visible route:

- index, create, detail, settings, logs, metrics, backup, execution, and danger
  routes must share the same navigation hierarchy and surface language;
- main-sidebar collection routes use the global shell without duplicating those
  destinations in a tab row;
- resource detail families use resource identity, status, and actions in the
  global topbar, and put every sibling route in the grouped settings sidebar;
- create and edit routes stay inside the same sidebar family instead of
  falling back to an isolated legacy page;
- reusable partials, empty states, confirmation flows, and row editors must be
  updated with the page that exposes them;
- audit the whole family for native selects, legacy heading blocks, old Save
  buttons, old status chips, and `coolbox`/`navbar-main`/`sub-menu-wrapper`
  to keep the family consistent.

Do not leave a sibling route using old tabs, a large in-flow title, a browser
select, or a different modal anatomy.

The New Resource page keeps its filter controls in the top layer card, then
renders Applications, Databases, and Services as separate layer-card sections.
Do not leave category headings and resource grids floating as uncontained
content below the filter card.

### Settings workspace

Application, service, database, server, and top-level settings pages use the
same 210px grouped, icon-led sidebar and a full-width content column. From `xl`
the sidebar is a fixed full-height black rail below the topbar that tracks the
main sidebar width, with a `white/[0.08]` right border, filter input, and
scroll. Its rows use the same nav pills as the main sidebar. Below
`xl` it becomes a wrapped grid of links above the content. Do not use the legacy
`sub-menu-wrapper`, native mobile page selects, or a row of top-level tabs. Only
show nested section anchors when a page has at least four useful sections.

The shared workspace grid is:

```blade
<section class="application-settings-workspace w-full max-w-none">
    <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-application.configuration-sidebar :application="$application" :current-route="$currentRoute" />
        <div class="min-w-0">
            ...
        </div>
    </div>
</section>
```

Instance Settings uses `x-settings.layout` with the same full-width workspace.

**Page titles (global):** `x-dashboard.navbar` H1s (`titleOnDesktop="false"`,
the default) hide at **lg+** only when the page renders a fixed tab strip or
actions; otherwise they stay visible. Collection indexes (Servers, Projects, …)
always keep their H1; stack title above actions on narrow widths so they never
overlap. Application, service, and database names render in-flow below `xl`
(the desktop action HUD breakpoint), server names below `lg`, and settings
families use `settings-mobile-header` below `xl`. Fixed tab-strip spacers must
be `lg:h-12` to match the bar height. Do not put the H1 beside the settings sidebar.

Standard content stack:

```blade
<div class="application-settings-workspace flex flex-col gap-6">
    <x-application.settings-section ... />
    <x-application.settings-section ... />
</div>
```

The current cross-page section gap is `gap-6`. Do not introduce extra top
padding on an individual page unless its toolbar is intentionally separated
from the first card.

Use a flex or grid stack with `gap-6`; do not use `space-y-*` between layer
cards. The layer-card root intentionally resets its own margin, so margin-based
spacing utilities can silently collapse.

---

## 6. Layer cards

Use `resources/views/components/application/settings-section.blade.php`.
Older manual shells may use `.application-settings-section-header` and
`.application-settings-section-body`; both must retain the same padded,
action-aligned anatomy as the component. Use the component for new work and
replace a manual shell when modifying it instead of creating another variant.

```blade
<x-application.settings-section
    id="public-access-section"
    title="Public access"
    helper="How this section affects the resource.">
    <x-slot:actions>
        <x-forms.button>Action</x-forms.button>
    </x-slot:actions>

    ...
</x-application.settings-section>
```

Anatomy (the Coolify tile):

- 18px radius, `#161618` fill, inset `white/18` ring, no shadow;
- the header sits on the same plane: 14px medium `fg` title, 12px muted
  description;
- a `white/6` hairline separates the header from the body (only when a header
  exists);
- 20px body padding on the same fill (no nested panel);
- optional `flush` mode for full-bleed tables; its bottom corners follow the
  card radius;
- card-level actions belong in the header slot;
- a section scrolled into view from the settings nav flashes a `white/30` ring.

Header actions use a 10px top/right inset while the title keeps its 20px left
inset. Do not leave a larger empty strip between the final action and the
card's top-right corner.

Do not split one collection into a summary card followed by a table or log
card. Keep its status/action in the header, its view switcher or toolbar at the
top of a flush body, and its data in that same layer card. Repeated file
editors are the opposite case: each file gets its own titled layer card so its
content and actions remain clearly associated.

### Nested radii

Concentric boxes must follow:

```text
outer radius = inner radius + visible inset
```

Examples:

- a 10px listbox option inside 4px padding uses a 14px panel;
- pill buttons inside pills stay pills (`9999px` absorbs any inset);
- an 18px card hosting a 14px code well uses a 4px inset.

Do not give visibly inset parent and child boxes the same radius. Flush or
edge-to-edge children are exempt because there is no visible inset to add.

Use an empty state when the section has no usable controls:

```blade
<x-empty size="sm" title="Nothing here" description="Explain what enables it.">
    <x-slot:icon>
        <x-reicon name="layers" class="size-8" />
    </x-slot:icon>
</x-empty>
```

---

## 7. Controls

Buttons, tabs and chips are 32px pills (28px for compact tabs and toolbar
icons). Text fields and listbox triggers are 36px high with a 10px radius,
`white/3` fill and a `white/10` outline; focus brightens the outline to
`white/40` and adds a 3px `white/8` halo; `aria-invalid` swaps both to
destructive red. A field with unsaved edits shows a 3px white inset bar on its
left edge.

### Field grids

The grid must match the controls visible in the current state:

- two visible peer controls use two columns, not a three-column grid with an
  empty track;
- three visible peer controls may use three columns when their content stays
  readable;
- conditional fields remain in the same grid when they are part of that field
  group, so a URL or text input does not become wider than its peer column;
- collapse to one column at smaller breakpoints.

Do not pick a column count from the maximum possible state if the normal state
shows fewer controls.

### Inputs

Use `x-forms.input` and `x-forms.textarea`. Fields need visible vertical spacing
between the label and control. Password visibility uses the outline Reicon
`eye`/`eye-off` treatment from the shared input component.

### Dropdowns

Do not use native `<select>` on application routes, including mobile fallbacks.
Use:

```blade
<x-forms.listbox id="property" label="Setting" :options="[
    ['value' => true, 'label' => 'Enabled'],
    ['value' => false, 'label' => 'Disabled'],
]" onChange="instantSave" />
```

Boolean checkboxes should normally become descriptive two-option listboxes.
Use `.live` behavior only when the selection needs an immediate server
rerender.

Keep checkboxes for compact permission matrices and multi-select lists. Those
controls must use the shared `x-forms.checkbox` anatomy: an 18px rounded custom
box with a `white/14` outline, a solid white checked fill, and a black check
mark. Never expose the browser or Tailwind Forms default
checkbox on application pages.

The popup panel is a `#1c1c1e` popover with a `white/10` outline and a 14px
radius around 10px options with a 4px inset; option hover is `white/6`. Keep
the option content left-aligned and size the panel to its content or trigger;
do not create an unnecessarily wide menu.

Every dropdown, menu and listbox panel uses the shared `--shadow-dropdown`
underlay. Do not hand-roll other drop shadows on a menu. Modals, the command
palette and the unsaved pill use `--shadow-window`.

Toolbar filter and sort buttons keep static labels (`Filter`, `Sort`). The
selected option is indicated inside the menu, not repeated on the trigger.

#### Livewire dropdown state synchronization

Instant-save listboxes must not flash back to an older value while Livewire is
saving or morphing the DOM. Treat the Alpine selection as the current visual
state until its request finishes:

- await the Livewire change handler and prevent overlapping selections while
  it is running;
- when a client-managed listbox can be rerendered by an unrelated or stale
  Livewire response, use the listbox's `preserveValue` option so the morph does
  not replace its newer Alpine value;
- scope `preserveValue` to controls whose value is owned by that interaction;
  do not use it when external server events must replace the displayed value;
- after saving through a related model, refresh the parent component's loaded
  relationship before rendering the response. A database write alone does not
  update an already-loaded Eloquent collection;
- use stable `wire:key` values for rows containing listboxes. Do not include the
  selected value in the key, because recreating the Alpine component causes a
  visible reset;
- remember that a portalled options panel is teleported outside its visual
  wrapper. Guard selection in the Alpine handler itself rather than relying
  only on `pointer-events` or a disabled wrapper.

The failure mode to avoid is: selection B is shown optimistically, selection A
is chosen next, the response for B morphs the listbox back to B, then the later
response finally shows A. The control should remain on the newest accepted
selection throughout the save sequence.

#### Multi-select filter dropdowns

Toolbar filters that can combine criteria use one multi-select listbox rather
than separate dropdowns or a single selected value. Follow the deployment
history filter in
`resources/views/livewire/project/application/deployment/index.blade.php`:

- set `aria-multiselectable="true"` on the listbox;
- group related options under compact uppercase labels;
- keep the dropdown open while options are toggled;
- use the shared 16px custom checkbox treatment: white checked fill and a black
  check mark;
- show the number of active selections in a small count pill on the static
  `Filter` trigger;
- combine selections within one group with OR logic and combine different
  groups with AND logic;
- constrain only the options area with `max-h-80 overflow-y-auto`;
- place a persistent `Reset filters` action in a separate footer below the
  scrollable options, divided by a top border;
- disable the reset action when no filter is active, and close the dropdown
  after resetting.

Do not represent the empty state as a selectable `All` option. The footer reset
action is the single way to return the multi-select to its unfiltered state.

### Standard table controls

Dense tables use the shared `x-table.*` components so search, filters, sorting,
and backend loading states remain visually and behaviorally consistent:

- `<x-table.toolbar>` owns the responsive search-left/actions-right layout;
- `<x-table.search>` owns the search icon, optional loading indicator, clear
  action, sizing, and input anatomy;
- `<x-table.filter>` owns the static Filter trigger, active-count pill,
  multi-select panel, scrollable options area, and Reset filters footer;
- `<x-table.sort>` owns the static Sort trigger and single-select panel;
- `<x-table.loading>` overlays only the changing table data for backend search,
  filter, sort, and pagination requests.

Tables continue to own their filter options, sort choices, headers, rows,
queries, permissions, and empty states. Backend-filtered or paginated tables
must use `x-table.loading`; frontend-only Alpine tables reuse the same toolbar
and control anatomy but do not show an artificial loading state.

### Buttons

| Variant | Recipe |
|---|---|
| Secondary (`.button`) | 32px pill, transparent, `border-white/15`, `text-fg/90`, `hover:bg-white/[0.06]` |
| Primary (`isHighlighted`, `.button-highlighted`) | solid white pill, black semibold text, `hover:bg-white/90` |
| Destructive (`isError`) | red text, `error/35` outline, `error/10` hover fill |
| Icon (`.icon-button`, `size-7` controls) | round, `fg-faint`, `hover:bg-white/[0.06]` |
| Split action | white pill split by a `black/15` divider; caret is the round end |
| Disabled | `opacity-60`, no hover change |

- one white pill per surface (page header, card, modal footer); everything
  next to it is an outline pill;
- arrow glyphs inside links nudge on hover (`group-hover:translate-x-0.5`);
- use outline Reicons where a matching glyph exists;
- avoid raw browser-default buttons and colored fills.

### Unsaved changes

`resources/views/components/unsaved-bar.blade.php` is a compact floating
bottom-center pill. It contains:

- “You have changes that haven't been saved yet.”
- an outline Reset pill;
- the white Save changes pill with an `Enter` kbd hint.

The shell is glass (`bg-[#0c0c0d]/95 backdrop-blur-2xl`, `ring-white/[0.12]`,
`--shadow-window`). On small viewports it is a 22px-radius sheet inset by
`inset-x-3` and stacks: full label on the first line, Reset / Save on the
second (right-aligned). From `sm` up it becomes a centered single-row
`rounded-full` pill.

Do not restore the old full-width footer.

Deferred fields in one Livewire component use one floating unsaved bar and one
submit action. Do not add a separate “Save configuration” button to every
card. Selectors that are safe to persist independently should use the existing
instant-save pattern. When those requests share a component with a modal draft,
pass the unsaved bar a `dirty` Alpine expression comparing that draft with its
initial values, so unrelated saves do not hide pending changes. Mount modal save
bars only while the modal is open to avoid inactive keyboard shortcuts.

---

## 8. Dense tables

Collections with many rows should use the Cloudflare-inspired table pattern:

- toolbar above the table;
- search on the left;
- filters, sort, view toggles, and Add on the right;
- 40px header row and roughly 48px data rows;
- subtle row hover;
- plain text or the shared status badge rather than large colored chips;
- compact action at the far right;
- no separate layer card for each item.

Do not add a summary card above a table when it only repeats the row count,
current page, or refresh interval. Keep counts and pagination in the footer.
Background polling stays silent unless its state is actionable; do not add a
“Live updates” badge just to explain that a table refreshes. Filters only
render meaningful values; use the shared listbox instead of a number input or
browser-native control.

The footer is always inside the table shell:

- `Showing X–Y of Z` on the left;
- first, previous, current page, next, and last controls on the right.

Hide the entire pagination footer when there is only one page (`totalPages > 1`).
A lone “1–2 of 2” bar with disabled controls adds noise and is unnecessary.

Use `x-status-badge` for resource and execution state. It is a small neutral
pill with a semantic dot, not a full colored rectangle.

Relevant classes:

- `.data-table`
- `.data-table-header`
- `.data-table-row`
- `.table-badge`

Create a page-specific grid class when columns differ. Add responsive rules
that hide secondary columns before allowing horizontal overflow.

---

### Domain rows on mobile

Domain tables become compact summary cards below 600px. Keep the public URL on
its own line, followed by a short routing summary such as `HTTP → HTTPS · Port
80 · Noindex`. Put DNS status and the existing icon actions on the final row.
Do not squeeze desktop label/value columns into a mobile card or move settings
behind an overflow menu. Long domains wrap, and icon actions retain 40px touch
targets.

## 9. Modals, confirmations, and toasts

### Modals

`x-modal-input` and confirmation dialogs reuse the layer-card shell as a
floating glass panel (`.application-settings-section.application-settings-form`):

- 22px radius, `rgb(12 12 13 / 0.95)` with `backdrop-blur`, inset `white/10`
  ring and `--shadow-window`;
- `bg-black/60 backdrop-blur-sm` scrim;
- header on the same plane, a `white/6` hairline above the body, and a round
  ringed close button;
- content-width desktop sizing;
- shared pill buttons and 36px fields;
- no redundant description below a self-explanatory title;
- custom listboxes instead of native browser selects;
- listbox and dropdown panels must render above the modal body and escape its
  scroll container. Never clip a panel at the modal boundary or make users
  scroll the modal to see its options;
- when there is not enough viewport space below the trigger, open the panel
  above it while keeping the panel visually on top of the modal;
- right-aligned footer actions below a divider;
- compact action buttons, never a submit button stretched by a column layout.

Edit modals should use the same field layout and option set as their matching
create modal.

### Command palette

The global search command palette (`livewire:global-search`) is a compact
top-anchored overlay:

- 22px glass panel (`rgb(12 12 13 / 0.95)`, blur, inset `white/10` ring,
  `--shadow-window`), the Coolify analysis-panel recipe;
- transparent 52px header with outline search glyph and 15px input;
- compact OS-aware mod+K (`⌘K` on macOS, `Ctrl+K` on Windows/Linux) / `/` / `ESC` kbd chips (10px, `ring-white/10`, 4px radius);
- results separated by `white/6` hairlines, group labels as uppercase overlines;
- dense result rows as inset 10px-radius rows (listbox anatomy), not full-bleed
  bars with global focus rings;
- hover uses `white/5`; keyboard focus uses `white/8` (no rail, no ring);
- create rows use a neutral plus tile that brightens (`white/8` fill,
  `white/15` ring) when the row is focused;
- type pills and quickcommand chips stay recessed and brighten on the focused
  row;
- neutral thin scrollbar inside the results body (not brand-colored);
- create-resource modals opened from the palette reuse the standard
  `application-settings-section` layer-card shell.

Preserve keyboard navigation (arrow keys, Enter via focused links, Escape to
clear then close), `/` and mod+K (⌘K / Ctrl+K by OS) open shortcuts, and the multi-step
server → destination → project → environment create flow.

### Toasts

`resources/views/components/toast.blade.php` provides the global
`window.toast(message, options)` API and Livewire event handling.

Current toast behavior:

- compact `surface-popover` card (`#242427`, inset `white/10` ring), 18px
  radius, maximum width 26rem;
- ringed 10px Reicon status tile: success keeps a green glyph, warning uses an
  amber glyph and tinted tile, danger is red, info/default are neutral;
- title plus optional description;
- dismiss and copy-details actions;
- normally up to four stacked notifications, without evicting persistent notices;
- four-second dismissal, paused while hovered;
- `persistent: true` disables automatic dismissal, including after hover; users close these notices with the dismiss button;
- support for all six screen positions and sanitized custom HTML.

Do not bring back the old oversized dark rectangle.

---

## 10. Terminals, logs, and metrics

### Terminals

Application and server browser terminals use the same browser-oriented console
shell, theme picker, compact header controls, and outline `browser-terminal`
Reicon. Hide a container switcher when only one container exists.

The themed console shell belongs to an open session. Before a target is
selected, the global Terminal page stays a normal top-level destination: a
full-width layer card titled `Start a terminal session`, its filter input in
the card header actions, and grouped `Servers` / `Containers` rows reusing the
command-palette row classes. Do not render an empty full-height console canvas
just to host the target picker, and do not offer the console theme selector
before a session owns that canvas. Rows show the target name, a muted server
column that only appears when the team has more than one server, and the shared
chevron. Group headers stick to the top of the scrolling list and carry a count.

### Logs

Runtime and deployment logs should feel like a clean terminal surface:

- keep a single log stream inside one layer card instead of adding an
  introductory card above it;
- one compact toolbar;
- a recessed monospace log viewport;
- search and line-count controls aligned with icon actions;
- clear live/follow state;
- fullscreen support without changing the control language;
- custom listbox-style menus instead of browser dropdowns.

### Metrics

Metrics pages use separate layer cards for range selection, CPU, and memory.
Charts follow the application metrics implementation:

- 240px area chart;
- monochrome series (`cpuColor` white, `ramColor` `#8e8e93`) with a smooth 2px
  stroke and restrained gradient fill;
- dashed neutral grid;
- no ApexCharts toolbar;
- tooltip positioned at the hovered point;
- UTC on both axes and tooltip;
- 20% headroom above observed values;
- downsample long time ranges before rendering.

Only add a metric if Sentinel exposes historical data for it. Current Sentinel
history endpoints store CPU and memory. Root filesystem usage is included in
the periodic push payload for threshold notifications, but it is not stored as
a historical Sentinel metric and has no history endpoint, so it cannot power a
disk-usage graph yet.

---

## 11. Current reference surfaces

Use these as implementation references:

| Surface | Reference |
|---|---|
| Dashboard overview | `resources/views/livewire/dashboard.blade.php` |
| Top-level collection cards | `resources/views/livewire/project/index.blade.php`, `resources/views/source/all.blade.php` |
| Top-level settings families | `resources/views/components/team/settings-layout.blade.php`, `resources/views/components/notification/settings-layout.blade.php` |
| General settings and form anatomy | `resources/views/livewire/project/application/general.blade.php` |
| Advanced settings | `resources/views/livewire/project/application/advanced.blade.php` |
| Resource actions in the topbar | `resources/views/livewire/project/application/heading.blade.php`, `resources/views/components/split-action.blade.php`, `resources/views/livewire/server/navbar.blade.php` |
| Grouped settings sidebar | `resources/views/components/application/configuration-sidebar.blade.php`, `resources/views/components/server/sidebar.blade.php` |
| Dense environment table and footer | `resources/views/livewire/project/shared/environment-variable/all.blade.php` |
| Standard table toolbar controls | `resources/views/components/table/*` |
| Application metrics charts | `resources/views/livewire/project/shared/metrics.blade.php` |
| Browser terminal workspace | `resources/views/livewire/terminal/index.blade.php` |
| Layer card | `resources/views/components/application/settings-section.blade.php` |
| Custom dropdown | `resources/views/components/forms/listbox.blade.php` |
| Empty state | `resources/views/components/empty.blade.php` |
| Status pill | `resources/views/components/status-badge.blade.php` |
| Floating save pill | `resources/views/components/unsaved-bar.blade.php` |
| Global toast | `resources/views/components/toast.blade.php` |
| Command palette / global search | `resources/views/livewire/global-search.blade.php` |
| Outline icons | `resources/views/components/reicon.blade.php` |
| Shared styling | `resources/css/app.css`, `resources/css/utilities.css` |
| Explicit scroll navigation | `resources/js/smooth-scroll.js` |
| Auth canvas | `resources/views/components/auth/shell.blade.php` |
| HTTP error pages | `resources/views/components/error-page.blade.php`, `resources/views/errors/*` |

HTTP error pages (400, 401, 402, 403, 404, 419, 429, 500, 503) use the shared
`<x-error-page>` component on the public auth-style canvas: a Geist Mono
status code with the white gradient (red gradient for server errors), compact title and muted description, neutral `.button` actions, and an
`auth-text-link`-style Contact support link. Keep copy sentence-case and avoid
oversized 200px status numbers.

---

## 12. UI implementation checklist

1. Inventory every route and reusable partial in the family before editing.
2. Read the current Blade and Livewire class before changing presentation.
3. Preserve every existing action, authorization check, loading state, and
   confirmation.
4. Add the grouped settings sidebar and scoped workspace/form class.
5. Convert meaningful groups to layer cards and use `gap-6`.
6. Make the responsive column count match the controls visible in every state.
7. Replace native selects and checkbox-style configuration with listboxes.
8. Use one save model per component: instant-save or one floating dirty bar.
9. Check nested radii using `outer = inner + inset`.
10. Keep modal descriptions purposeful and footer actions compact/right-aligned.
11. Use tables for dense collections and cards for forms or summaries.
12. Use `x-status-badge`, `x-empty`, and `x-reicon`.
13. Confirm the neutral hierarchy preserves green health, amber warnings,
    red errors, and syntax colors; verify enabled controls clear 3:1 contrast.
14. Check fixed-nav anchor offsets and responsive stacking.
15. Sweep every sibling route for legacy controls and shells.
16. Run `git diff --check`.
17. Compile Blade views with `./scripts/dev exec php artisan view:cache`.
18. Build assets with `npm run build`.
19. Hard-refresh and inspect the family routes, including with
    `prefers-reduced-motion` and on a touch device (native scrolling).
