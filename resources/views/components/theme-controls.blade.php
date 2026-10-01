@props(['variant' => 'full'])

@php($checkPath = 'm2.5 6.25 2.1 2.1 4.9-5')

{{-- Coolify is dark-only, so the appearance controls only cover layout
     preferences. All state comes from the shared window.themeControls(). --}}
@if ($variant === 'menu')
    {{-- Compact list for the profile dropdown. The parent controls visibility. --}}
    <div x-data="themeControls()" class="grid gap-0.5">
        <div class="px-2 pt-1 pb-0.5 text-[10px] font-medium tracking-wide text-fg-faint uppercase">
            Page width
        </div>
        @foreach ([
            ['value' => 'full', 'label' => 'Full width'],
            ['value' => 'centered', 'label' => 'Centered'],
        ] as $option)
            <button type="button" @click="setWidth('{{ $option['value'] }}')"
                class="flex h-8 w-full items-center justify-between rounded-full px-2.5 text-left text-xs text-fg-dim transition-colors hover:bg-white/[0.06] hover:text-fg">
                <span>{{ $option['label'] }}</span>
                <svg x-show="pageWidth === '{{ $option['value'] }}'" class="size-3.5 text-fg" viewBox="0 0 12 12"
                    fill="none" aria-hidden="true">
                    <path d="{{ $checkPath }}" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        @endforeach
    </div>
@else
    {{-- Card grid for the Appearance settings page. --}}
    <div x-data="themeControls()" class="mt-8 flex w-full max-w-none flex-col gap-6 lg:mt-3">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>Page width</h2>
                    <p>Choose how content uses the available browser width.</p>
                </div>
            </div>
            <div class="application-settings-section-body grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['value' => 'full', 'label' => 'Full width', 'description' => 'Use all available space for page content.'],
                    ['value' => 'centered', 'label' => 'Centered', 'description' => 'Keep content centered at a comfortable maximum width.'],
                ] as $option)
                    <button type="button" @click="setWidth('{{ $option['value'] }}')"
                        class="group overflow-hidden rounded-2xl bg-surface text-left ring-1 ring-inset transition-[background-color,box-shadow] hover:bg-white/[0.04]"
                        :class="pageWidth === '{{ $option['value'] }}' ? 'ring-white/30' : 'ring-hairline'">
                        <div class="flex h-20 items-center border-b border-white/[0.06] px-4">
                            <div class="flex h-11 w-full gap-1.5 rounded-lg bg-[#0a0a0a] p-1.5 ring-1 ring-inset ring-white/10">
                                <div class="w-3 shrink-0 rounded-sm bg-white/10"></div>
                                <div @class([
                                    'h-full rounded-sm bg-white/10',
                                    'w-full' => $option['value'] === 'full',
                                    'mx-auto w-2/3' => $option['value'] === 'centered',
                                ])></div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-medium text-fg">{{ $option['label'] }}</span>
                                <x-reicon name="check-circle" class="size-4 text-fg"
                                    x-show="pageWidth === '{{ $option['value'] }}'" x-cloak />
                            </div>
                            <p class="mt-1 text-xs leading-5 text-fg-faint">{{ $option['description'] }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>
    </div>
@endif
