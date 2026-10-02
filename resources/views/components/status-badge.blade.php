@props([
    'label' => null,
    'status' => null,
    'type' => 'neutral',
    'as' => 'span',
    'dynamic' => false,
])

@php
    $dotClasses = [
        'neutral' => 'bg-white/30',
        'success' => 'bg-success',
        'warning' => 'bg-warning',
        'error' => 'bg-error',
    ];

    $baseClasses = 'inline-flex h-6 max-w-full items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 text-xs font-normal leading-none text-fg-dim ring-1 ring-inset ring-white/[0.08]';
@endphp

@if ($as === 'button')
    <button {{ $attributes->class([$baseClasses, 'transition-colors'])->merge(['type' => 'button', 'data-status-type' => $type]) }}>
        @if ($dynamic)
            {{ $slot }}
        @else
            <span class="size-1.5 shrink-0 rounded-full {{ $dotClasses[$type] ?? $dotClasses['neutral'] }}"></span>
            <span class="truncate">{{ collect([$label, $status])->filter()->join(' ') }}</span>
        @endif
    </button>
@elseif ($as === 'a')
    <a {{ $attributes->class([$baseClasses, 'transition-colors'])->merge(['data-status-type' => $type]) }}>
        @if ($dynamic)
            {{ $slot }}
        @else
            <span class="size-1.5 shrink-0 rounded-full {{ $dotClasses[$type] ?? $dotClasses['neutral'] }}"></span>
            <span class="truncate">{{ collect([$label, $status])->filter()->join(' ') }}</span>
        @endif
    </a>
@else
    <span {{ $attributes->class([$baseClasses])->merge(['data-status-type' => $type]) }}>
        @if ($dynamic)
            {{ $slot }}
        @else
            <span class="size-1.5 shrink-0 rounded-full {{ $dotClasses[$type] ?? $dotClasses['neutral'] }}"></span>
            <span class="truncate">{{ collect([$label, $status])->filter()->join(' ') }}</span>
        @endif
    </span>
@endif
