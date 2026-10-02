@props([
    'type' => 'warning',
    'title' => 'Warning',
    'class' => '',
    'dismissible' => false,
    'onDismiss' => null,
])

@php
    $styles = [
        'warning' => [
            'icon' => 'alert-triangle',
            'shell' => 'bg-warning/[0.06] ring-warning/40',
            'iconClass' => 'text-warning',
            'titleClass' => 'text-warning',
            'textClass' => 'text-fg-dim',
        ],
        'danger' => [
            'icon' => 'alert-circle',
            'shell' => 'bg-error/[0.06] ring-error/30',
            'iconClass' => 'text-error',
            'titleClass' => 'text-error',
            'textClass' => 'text-fg-dim',
        ],
        'info' => [
            'icon' => 'info-circle',
            'shell' => 'bg-white/[0.02] ring-hairline',
            'iconClass' => 'text-fg-faint',
            'titleClass' => 'text-fg',
            'textClass' => 'text-fg-faint',
        ],
        'success' => [
            'icon' => 'check-circle',
            'shell' => 'bg-white/[0.02] ring-white/[0.1]',
            'iconClass' => 'text-success',
            'titleClass' => 'text-fg',
            'textClass' => 'text-fg-faint',
        ],
    ];

    $style = $styles[$type] ?? $styles['warning'];
@endphp

<div
    {{ $attributes->merge(['data-callout-type' => $type, 'class' => 'relative rounded-2xl px-4 py-3 ring-1 ring-inset ' . $style['shell'] . ' ' . $class]) }}>
    <div class="flex items-start gap-2.5">
        <x-reicon :name="$style['icon']" class="mt-0.5 size-4 shrink-0 {{ $style['iconClass'] }}" />
        <div class="min-w-0 flex-1 {{ $dismissible ? 'pr-7' : '' }}">
            <div class="text-[13px] font-medium {{ $style['titleClass'] }}">{{ $title }}</div>
            <div class="mt-0.5 text-[12.5px] leading-5 {{ $style['textClass'] }}">{{ $slot }}</div>
        </div>
        @if ($dismissible && $onDismiss)
            <button type="button" @click.stop="{{ $onDismiss }}"
                class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-full transition-colors hover:bg-white/[0.06]"
                aria-label="Dismiss">
                <x-reicon name="x" class="size-3.5 {{ $style['iconClass'] }}" />
            </button>
        @endif
    </div>
</div>
