@extends('layouts.base')
@section('body')
    @parent
    @if (isSubscribed() || !isCloud())
        <livewire:layout-popups />
    @endif
    <!-- Global search component - included once to prevent keyboard shortcut duplication -->
    <livewire:global-search />
    @auth
        <div x-data="{
            open: false,
            hasSidebarPreference: localStorage.getItem('sidebarCollapsed') !== null,
            userCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            autoCollapse: localStorage.getItem('sidebarAutoCollapse') !== 'false',
            hasSecondBar: false,
            collapsed: false,
            pageWidth: localStorage.getItem('pageWidth') || 'full',
            sidebarReady: false,
            init() {
                this.applyCollapsed(false);
                this.$nextTick(() => {
                    requestAnimationFrame(() => {
                        this.sidebarReady = true;
                    });
                });
            },
            targetCollapsed() {
                this.hasSecondBar = !!document.querySelector('.application-settings-navigation');
                return this.hasSidebarPreference ? this.userCollapsed : (this.autoCollapse && this.hasSecondBar);
            },
            applyCollapsed(animate) {
                const target = this.targetCollapsed();
                if (target === this.collapsed) return;
                if (animate) {
                    // Let the new page paint at the current width, then animate the
                    // slide a frame later so the auto-collapse reads as a motion.
                    this.sidebarReady = true;
                    requestAnimationFrame(() => requestAnimationFrame(() => { this.collapsed = target; }));
                } else {
                    this.collapsed = target;
                }
            },
            toggleSidebar() {
                this.collapsed = !this.collapsed;
                this.hasSidebarPreference = true;
                this.userCollapsed = this.collapsed;
                localStorage.setItem('sidebarCollapsed', this.userCollapsed);
            },
            toggleAutoCollapse() {
                this.autoCollapse = !this.autoCollapse;
                localStorage.setItem('sidebarAutoCollapse', this.autoCollapse);
                this.applyCollapsed(true);
            }
        }" @open-global-search.window="open = false" @page-width-changed.window="pageWidth = $event.detail" x-on:livewire:navigated.window="applyCollapsed(true)"
            :style="{ '--sidebar-w': collapsed ? '4rem' : '14rem' }" x-cloak
            class="text-inherit">
            {{-- ============ DESKTOP TOP BAR ============ --}}
            <header
                x-data="{ resourceActionsOpen: false }"
                @resource-actions-toggled.window="resourceActionsOpen = $event.detail.open"
                :class="{ 'z-[1000]': resourceActionsOpen }"
                class="hidden lg:flex fixed top-0 inset-x-0 z-50 h-12 items-center border-b border-white/[0.08] bg-panel/90 backdrop-blur-2xl backdrop-saturate-150">
                {{-- Brand (width tracks sidebar) --}}
                <div class="flex items-center gap-2 h-full shrink-0 border-r border-white/[0.08] transition-[width] duration-200"
                    :class="collapsed ? 'w-16 justify-center px-0' : 'w-56 px-4'">
                    <div class="flex shrink-0 items-baseline gap-1.5 min-w-0">
                        <a href="/" {{ wireNavigate() }} title="Coolify"
                            class="flex items-center gap-2 text-fg/90 transition-opacity hover:opacity-70">
                            <img src="/coolify-logo-monochrome.svg" alt="" class="size-[18px] shrink-0 invert" />
                            <span x-show="!collapsed" class="text-[15px] font-semibold tracking-[-0.01em]">Coolify</span>
                        </a>
                        <x-version x-show="!collapsed"
                            class="!text-[10.5px] font-mono font-normal text-fg-faint !opacity-100 hover:!opacity-100 hover:text-fg" />
                    </div>
                    @if (isInstanceAdmin() && !isCloud())
                        <div x-show="!collapsed" class="ml-auto shrink-0">
                            @persist('upgrade')
                                <livewire:upgrade />
                            @endpersist
                        </div>
                    @endif
                </div>
                {{-- Collapse toggle + team switcher --}}
                <div
                    class="flex h-full items-center gap-0.5 min-w-0 flex-1 pl-3 pr-4">
                    <div class="relative flex min-w-0 flex-1 items-center">
                        <x-top-breadcrumb />
                        <div id="server-topbar-context" class="min-w-0"></div>
                    </div>
                    {{-- Dev Server-Timing HUD docks here (local only; empty in production) --}}
                    <div id="server-timing-hud-slot" data-server-timing-hud-slot class="hidden shrink-0 items-center"></div>
                    <div id="configuration-warning-hud-slot" class="relative shrink-0"></div>
                    {{-- Resource actions dock here on desktop. --}}
                    <div id="resource-action-hud-slot" class="hidden shrink-0 items-center xl:flex"></div>
                </div>
            </header>

            {{-- ============ MOBILE SLIDE-OVER SIDEBAR (shadcn-style sheet) ============ --}}
            <div class="mobile-sidebar-sheet relative z-[1000] lg:hidden"
                x-on:keydown.escape.window="open = false"
                x-on:livewire:navigated.window="open = false">
                {{-- Scrim: fades in/out --}}
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" x-show="open" x-cloak x-on:click="open = false"
                    x-transition:enter="transition-opacity ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
                {{-- Panel: slides in from the right (iOS drawer curve), exits faster --}}
                <div class="fixed inset-y-0 right-0 flex" :class="!open && 'pointer-events-none'">
                    <div x-show="open" x-cloak x-trap.inert.noscroll="open"
                        role="dialog" aria-modal="true" aria-label="Navigation menu"
                        x-transition:enter="transform transition ease-[cubic-bezier(0.32,0.72,0,1)] duration-300"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in duration-200"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                        class="relative flex h-full w-72 max-w-[85vw] min-w-0 flex-col overflow-hidden rounded-l-[22px] bg-panel ring-1 ring-inset ring-white/[0.1]">
                        <div data-mobile-sidebar-brand
                            class="flex h-12 shrink-0 items-center justify-between gap-1.5 border-b border-white/[0.08] px-4">
                            <div class="flex min-w-0 items-baseline gap-1.5">
                                <a href="/" {{ wireNavigate() }} title="Coolify"
                                    class="text-[15px] font-semibold tracking-[-0.01em] text-fg/90 transition-opacity hover:opacity-70">
                                    Coolify
                                </a>
                                <x-version class="!text-[10.5px] font-mono font-normal text-fg-faint !opacity-100 hover:!opacity-100 hover:text-fg" />
                            </div>
                            <button type="button" x-on:click="open = false" aria-label="Close menu"
                                class="-mr-1.5 flex size-7 shrink-0 items-center justify-center rounded-full text-fg-dim ring-1 ring-inset ring-white/[0.08] transition-colors hover:bg-white/[0.06] hover:text-fg active:scale-95">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                    stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex min-h-0 min-w-0 flex-1 flex-col overflow-y-auto pb-2 scrollbar">
                            <x-navbar />
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ DESKTOP SIDEBAR (below top bar) ============ --}}
            <div class="hidden lg:fixed lg:top-12 lg:bottom-0 lg:left-0 lg:z-40 lg:flex lg:flex-col min-w-0"
                :class="[collapsed ? 'lg:w-16' : 'lg:w-56', sidebarReady ? 'transition-[width] duration-200' : '']">
                <div class="flex grow min-w-0 flex-col overflow-visible">
                    <x-navbar deployments-indicator />
                </div>
            </div>

            {{-- ============ MOBILE TOP BAR ============ --}}
            <div
                class="sticky top-0 z-40 flex items-center justify-between px-4 py-2.5 gap-x-4 sm:px-6 lg:hidden bg-panel/90 backdrop-blur-2xl backdrop-saturate-150 border-b border-white/[0.08]">
                <div class="flex min-w-0 flex-1 items-center gap-2.5">
                    <a href="/"
                        class="flex size-8 shrink-0 items-center justify-center rounded-[10px] bg-white/[0.04] ring-1 ring-inset ring-white/10 transition-opacity hover:opacity-70">
                        <img src="/coolify-logo-monochrome.svg" alt="Coolify" class="size-4 invert" />
                    </a>
                    <div class="min-w-0" x-data="{ collapsed: false }">
                        <livewire:switch-team />
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    {{-- Dev Server-Timing HUD docks here on <lg (desktop uses #server-timing-hud-slot) --}}
                    <div id="server-timing-hud-slot-mobile" data-server-timing-hud-slot
                        class="hidden shrink-0 items-center"></div>
                    <div id="configuration-warning-hud-slot-mobile" class="relative shrink-0"></div>
                    <livewire:deployments-indicator variant="mobile" />
                    @if (isInstanceAdmin() && !isCloud())
                        <livewire:upgrade key="mobile-upgrade" />
                    @endif
                    <x-top-user-menu />
                    <button type="button" x-on:click="open = !open"
                        class="flex size-7 items-center justify-center rounded-full text-fg-dim ring-1 ring-inset ring-white/[0.08] transition-[transform,background-color] duration-150 ease-out hover:bg-white/[0.05] hover:text-fg active:scale-90">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6" />
                            <path d="M9 4v16" stroke="currentColor" stroke-width="1.6" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ============ MAIN ============ --}}
            <main
                class="app-canvas relative isolate min-h-screen bg-app px-5 py-6 sm:px-8 lg:px-10 lg:pt-[calc(3rem+2rem)] lg:pb-16"
                :class="[collapsed ? 'lg:ml-16' : 'lg:ml-56', sidebarReady ? 'transition-[margin] duration-200' : '']">
                <div class="w-full" :class="pageWidth === 'centered' ? 'mx-auto max-w-[1400px]' : 'max-w-none'">
                    {{ $slot }}
                </div>
            </main>
        </div>
    @endauth
@endsection
