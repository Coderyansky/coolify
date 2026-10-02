@props([
    'title',
    'description' => null,
])

<section class="auth-shell application-settings-form">
    <div class="auth-shell-content">
        <div class="auth-panel">
            <div class="auth-card-heading">
                <span class="auth-mark" aria-hidden="true">
                    <img src="/coolify-logo-monochrome.svg" alt="" class="size-5 invert" />
                </span>
                <h1>{{ $title }}</h1>
                @if ($description)
                    <p>{{ $description }}</p>
                @endif
            </div>

            <div class="auth-card">
                <div class="auth-card-body">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <footer class="auth-card-footer">
                        {{ $footer }}
                    </footer>
                @endisset
            </div>
        </div>
    </div>
</section>
