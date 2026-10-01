@props([
    'title',
    'description' => null,
    'size' => 'base', // sm | base | lg
    'iconName' => null, // reicon name; use with icon-name="…"
])

@php
    $minHeight = match ($size) {
        'sm' => 'min-h-44',
        'lg' => 'min-h-96',
        default => 'min-h-80',
    };

    $iconBox = match ($size) {
        'sm' => 'mb-3 size-10',
        'lg' => 'mb-4 size-12',
        default => 'mb-4 size-11',
    };

    $iconSize = match ($size) {
        'sm' => 'size-4.5',
        'lg' => 'size-6',
        default => 'size-5',
    };

    $titleClass = match ($size) {
        'sm' => 'text-[14px]! font-medium! tracking-normal! text-fg',
        'lg' => 'text-[17px]! font-semibold! tracking-[-0.01em]! text-fg',
        default => 'text-[15px]! font-semibold! tracking-[-0.01em]! text-fg',
    };

    $descriptionClass = match ($size) {
        'sm' => 'mt-1.5 max-w-sm text-[12.5px] leading-5 text-fg-faint',
        default => 'mt-1.5 max-w-sm text-[14px] leading-relaxed text-fg-faint',
    };

    $hasIconSlot = isset($icon) && $icon instanceof \Illuminate\View\ComponentSlot && ! $icon->isEmpty();
    $hasIconName = filled($iconName);
    $hasIcon = $hasIconSlot || $hasIconName;

    // Prefer contents; fall back to actions (legacy slot name used in several views).
    $footer = null;
    if (isset($contents) && $contents instanceof \Illuminate\View\ComponentSlot && ! $contents->isEmpty()) {
        $footer = $contents;
    } elseif (isset($actions) && $actions instanceof \Illuminate\View\ComponentSlot && ! $actions->isEmpty()) {
        $footer = $actions;
    }
@endphp

{{-- Empty state: ringed tile over a fading dot grid, icon tile, title,
     description, optional actions. --}}
<div
    {{ $attributes->merge([
        'class' => "empty-state relative isolate flex w-full flex-col items-center justify-center overflow-hidden rounded-2xl px-6 py-10 text-center ring-1 ring-inset ring-hairline {$minHeight}",
    ]) }}>
    @if ($hasIcon)
        <div
            class="{{ $iconBox }} flex items-center justify-center rounded-[14px] bg-black text-fg-dim ring-1 ring-inset ring-white/10">
            @if ($hasIconSlot)
                {{ $icon }}
            @else
                <x-reicon :name="$iconName" class="{{ $iconSize }}" />
            @endif
        </div>
    @endif

    <h2 class="{{ $titleClass }}">{{ $title }}</h2>

    @if ($description)
        <p class="{{ $descriptionClass }}">{{ $description }}</p>
    @endif

    @if ($footer)
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            {{ $footer }}
        </div>
    @endif
</div>
