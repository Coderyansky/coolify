@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'actionLabel' => 'View all',
    'icon' => 'arrow-right',
])

{{--
    Shared section heading: a 15px feature title, an optional 13px muted
    subtitle, and an optional right-aligned secondary pill whose arrow nudges
    right on hover. Pass `href` for the default "View all"
    button, or an `<x-slot:actions>` for a custom control.
--}}
<div {{ $attributes->merge(['class' => 'mb-4 min-w-0']) }}>
    <div class="flex items-center justify-between gap-4">
        <h2 class="min-w-0 truncate text-[15px]! leading-6! font-semibold! tracking-[-0.01em]! text-fg">
            {{ $title }}
        </h2>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @elseif (filled($href))
            <a href="{{ $href }}" {{ wireNavigate() }}
                class="button group">
                {{ $actionLabel }}
                @if ($icon)
                    <x-reicon :name="$icon" class="size-3 opacity-70 transition-transform duration-150 ease-out group-hover:translate-x-0.5" />
                @endif
            </a>
        @endisset
    </div>
    @if (filled($subtitle))
        <p class="mt-1 text-[13px] text-fg-faint">{{ $subtitle }}</p>
    @endif
</div>
