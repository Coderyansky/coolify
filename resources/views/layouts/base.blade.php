<!DOCTYPE html>
<html class="dark" data-theme="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<script data-navigate-once>
    // Apply this fork's dark palette without deleting saved preferences.
    (function () {
        window.applyStoredTheme = () => {
            document.documentElement.classList.add('dark');
            document.documentElement.dataset.theme = 'dark';
            document.documentElement.style.removeProperty('--theme-base-color');
            document.documentElement.style.removeProperty('--theme-accent-foreground');
        };
        // Single source for the layout preference Alpine state, shared by the
        // Appearance page and the profile dropdown via x-data="themeControls()".
        window.themeControls = () => ({
            pageWidth: localStorage.getItem('pageWidth') || 'full',
            setWidth(width) {
                this.pageWidth = width;
                localStorage.setItem('pageWidth', width);
                window.dispatchEvent(new CustomEvent('page-width-changed', { detail: width }));
            },
        });

        document.addEventListener('livewire:navigated', window.applyStoredTheme);
        window.applyStoredTheme();
    })();
</script>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#0a0a0a" id="theme-color-meta" />
    <meta name="color-scheme" content="dark" />
    <meta name="Description" content="Coolify: An open-source & self-hostable Heroku / Netlify / Vercel alternative" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:site" content="@coolifyio" />
    <meta name="twitter:title" content="Coolify" />
    <meta name="twitter:description" content="An open-source & self-hostable Heroku / Netlify / Vercel alternative." />
    <meta name="twitter:image" content="https://cdn.coollabs.io/og-images/coolify.png" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://coolify.io" />
    <meta property="og:title" content="Coolify" />
    <meta property="og:description" content="An open-source & self-hostable Heroku / Netlify / Vercel alternative." />
    <meta property="og:site_name" content="Coolify" />
    <meta property="og:image" content="https://cdn.coollabs.io/og-images/coolify.png" />
    @use('App\Models\InstanceSettings')
    @php

        $instanceSettings = instanceSettings();
        $name = null;

        if ($instanceSettings) {
            $displayName = $instanceSettings->getTitleDisplayName();

            if (strlen($displayName) > 0) {
                $name = $displayName . ' ';
            }
        }
    @endphp
    <title>{{ $name }}{{ $title ?? 'Coolify' }}</title>
    @env('local')
        <link rel="icon" href="{{ asset('coolify-logo-dev-transparent.png') }}" type="image/png" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32" />
        <link rel="icon" href="{{ asset('coolify-logo.svg') }}" type="image/svg+xml" />
    @endenv
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" />
    <meta name="apple-mobile-web-app-title" content="Coolify" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @if (config('app.name') == 'Coolify Cloud')
        <script defer data-domain="app.coolify.io" src="https://analytics.coollabs.io/js/plausible.js"></script>
        <script src="https://js.sentry-cdn.com/0f8593910512b5cdd48c6da78d4093be.min.js" crossorigin="anonymous"></script>
    @endif
    @auth
        <script type="text/javascript" src="{{ URL::asset('js/echo.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/pusher.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/apexcharts.js') }}"></script>
        <script type="text/javascript" src="{{ URL::asset('js/purify.min.js') }}"></script>
    @endauth
</head>
@section('body')

<body class="text-fg-dim">
    <x-toast />
    <x-icon-tooltip />
    <script data-navigate-once>
        // Global HTML sanitization function using DOMPurify
        window.sanitizeHTML = function (html) {
            if (!html) return '';
            const URL_RE = /^(https?:|mailto:)/i;
            const config = {
                ALLOWED_TAGS: ['a', 'b', 'br', 'code', 'del', 'div', 'em', 'i', 'mark', 'p', 'pre', 's', 'span', 'strong',
                    'u'
                ],
                ALLOWED_ATTR: ['class', 'href', 'target', 'title', 'rel'],
                ALLOW_DATA_ATTR: false,
                FORBID_TAGS: ['script', 'object', 'embed', 'applet', 'iframe', 'form', 'input', 'button', 'select',
                    'textarea', 'details', 'summary', 'dialog', 'style'
                ],
                FORBID_ATTR: ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur', 'onchange',
                    'onsubmit', 'ontoggle', 'style'
                ],
                KEEP_CONTENT: true,
                RETURN_DOM: false,
                RETURN_DOM_FRAGMENT: false,
                SANITIZE_DOM: true,
                SANITIZE_NAMED_PROPS: true,
                SAFE_FOR_TEMPLATES: true,
                ALLOWED_URI_REGEXP: URL_RE
            };

            // One-time hook registration (idempotent pattern)
            if (!window.__dpLinkHook) {
                DOMPurify.addHook('afterSanitizeAttributes', node => {
                    // Remove Alpine.js directives to prevent XSS
                    if (node.hasAttributes && node.hasAttributes()) {
                        const attrs = Array.from(node.attributes);
                        attrs.forEach(attr => {
                            // Remove x-* attributes (Alpine directives)
                            if (attr.name.startsWith('x-')) {
                                node.removeAttribute(attr.name);
                            }
                            // Remove @* attributes (Alpine event shorthand)
                            if (attr.name.startsWith('@')) {
                                node.removeAttribute(attr.name);
                            }
                            // Remove :* attributes (Alpine binding shorthand)
                            if (attr.name.startsWith(':')) {
                                node.removeAttribute(attr.name);
                            }
                        });
                    }

                    // Existing link sanitization
                    if (node.nodeName === 'A' && node.hasAttribute('href')) {
                        const href = node.getAttribute('href') || '';
                        if (!URL_RE.test(href)) node.removeAttribute('href');
                        if (node.getAttribute('target') === '_blank') {
                            node.setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                });
                window.__dpLinkHook = true;
            }
            return DOMPurify.sanitize(html, config);
        };

        const cpuColor = '#f5f5f7';
        const ramColor = '#8e8e93';
        const textColor = '#8e8e93';
        @auth
            window.Pusher = Pusher;
            const EchoConstructor = typeof Echo === 'function' ? Echo : Echo.default;
            window.Echo = new EchoConstructor({
                broadcaster: 'reverb',
                key: "{{ config('constants.pusher.app_key') }}" || 'coolify',
                wsHost: "{{ config('constants.pusher.host') }}" || window.location.hostname,
                wsPort: "{{ getRealtime() }}",
                wssPort: "{{ getRealtime() }}",
                forceTLS: window.location.protocol === 'https:',
                encrypted: true,
                enableStats: false,
                enableLogging: true,
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
                // Add auto reconnection settings
                enabledTransports: ['ws', 'wss'],
                disabledTransports: ['sockjs', 'xhr_streaming', 'xhr_polling'],
                // Attempt to reconnect on connection lost
                autoReconnect: true,
                // Wait 1 second before first reconnect attempt
                reconnectionDelay: 1000,
                // Maximum delay between reconnection attempts
                maxReconnectionDelay: 1000,
                // Multiply delay by this number for each reconnection attempt
                reconnectionDelayGrowth: 1,
                // Maximum number of reconnection attempts
                maxAttempts: 15
            });
        @endauth
        let checkHealthInterval = null;
        let checkIfIamDeadInterval = null;

        document.addEventListener('livewire:init', () => {
            window.Livewire.on('reloadWindow', (timeout) => {
                if (timeout) {
                    setTimeout(() => {
                        window.location.reload();
                    }, timeout);
                    return;
                } else {
                    window.location.reload();
                }
            })
            window.Livewire.on('info', (message) => {
                if (typeof message === 'string') {
                    window.toast('Info', {
                        type: 'info',
                        description: message,
                    })
                    return;
                }
                if (message.length == 1) {
                    window.toast('Info', {
                        type: 'info',
                        description: message[0],
                    })
                } else if (message.length == 2) {
                    window.toast(message[0], {
                        type: 'info',
                        description: message[1],
                    })
                }
            })
            window.Livewire.on('error', (message) => {
                if (typeof message === 'string') {
                    window.toast('Error', {
                        type: 'danger',
                        description: message,
                    })
                    return;
                }
                if (message.length == 1) {
                    window.toast('Error', {
                        type: 'danger',
                        description: message[0],
                    })
                } else if (message.length == 2) {
                    window.toast(message[0], {
                        type: 'danger',
                        description: message[1],
                    })
                }
            })
            window.Livewire.on('warning', (message) => {
                if (typeof message === 'string') {
                    window.toast('Warning', {
                        type: 'warning',
                        description: message,
                    })
                    return;
                }
                if (message.length == 1) {
                    window.toast('Warning', {
                        type: 'warning',
                        description: message[0],
                    })
                } else if (message.length == 2) {
                    window.toast(message[0], {
                        type: 'warning',
                        description: message[1],
                    })
                }
            })
            window.Livewire.on('success', (message) => {
                if (typeof message === 'string') {
                    window.toast('Success', {
                        type: 'success',
                        description: message,
                    })
                    return;
                }
                if (message.length == 1) {
                    window.toast('Success', {
                        type: 'success',
                        description: message[0],
                    })
                } else if (message.length == 2) {
                    window.toast(message[0], {
                        type: 'success',
                        description: message[1],
                    })
                }
            })
        });
    </script>
</body>
@show

</html>
