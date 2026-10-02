@props([
    'type' => 'info',
])

@php
    $styles = match ($type) {
        'success' => 'bg-white/[0.03] text-fg ring-white/15',
        'error' => 'bg-error/[0.08] text-error ring-error/30',
        'warning' => 'bg-warning/[0.06] text-warning ring-warning/40',
        default => 'bg-white/[0.02] text-fg-dim ring-hairline',
    };

    $icon = match ($type) {
        'success' => 'check-circle',
        'error' => 'alert-circle',
        'warning' => 'alert-triangle',
        default => 'info-circle',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl px-3.5 py-3 text-[13px] ring-1 ring-inset {$styles}"]) }}>
    <x-reicon :name="$icon" class="mt-0.5 size-4 shrink-0 {{ $type === 'success' ? 'text-success' : '' }}" />
    <div class="min-w-0 flex-1 leading-5">
        {{ $slot }}
    </div>
</div>
