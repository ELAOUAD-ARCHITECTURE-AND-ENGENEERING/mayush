@auth
    @if (config('notifications_v2.enabled') && storefront_asset('notifications.js'))
        @php
            $notificationFlashToasts = collect(session('flash_notification', collect()))
                ->map(function ($message) {
                    $level = $message['level'] ?? 'info';

                    return [
                        'title' => translate(ucfirst($level)),
                        'message' => (string) ($message['message'] ?? ''),
                        'category' => 'activity',
                        'severity' => in_array($level, ['danger', 'error'], true)
                            ? 'critical'
                            : (in_array($level, ['warning'], true) ? 'important' : 'info'),
                    ];
                })
                ->filter(fn ($toast) => $toast['message'] !== '')
                ->values();
        @endphp
        @if ($notificationFlashToasts->isNotEmpty())
            <script>
                window.addEventListener('DOMContentLoaded', function () {
                    @foreach ($notificationFlashToasts as $toast)
                        document.dispatchEvent(new CustomEvent('mayush:toast', { detail: @json($toast) }));
                    @endforeach
                }, { once: true });
            </script>
        @endif
        @once
            <style>
                .mayush-toast-region {
                    position: fixed;
                    right: 18px;
                    bottom: 18px;
                    z-index: 2147483000;
                    display: grid;
                    width: min(390px, calc(100vw - 36px));
                    gap: 10px;
                    pointer-events: none;
                }

                [dir="rtl"] .mayush-toast-region {
                    right: auto;
                    left: 18px;
                }

                .mayush-toast {
                    display: grid;
                    grid-template-columns: auto minmax(0, 1fr) auto;
                    gap: 11px;
                    align-items: start;
                    padding: 13px 12px;
                    color: #f8fafc;
                    background: #111827;
                    border: 1px solid rgba(203, 213, 225, .24);
                    border-radius: 10px;
                    box-shadow: 0 16px 36px rgba(15, 23, 42, .32);
                    pointer-events: auto;
                }

                .mayush-toast__indicator {
                    width: 9px;
                    height: 9px;
                    margin-top: 5px;
                    background: #14b8a6;
                    border-radius: 50%;
                }

                .mayush-toast--important .mayush-toast__indicator {
                    background: #d97434;
                }

                .mayush-toast--critical .mayush-toast__indicator {
                    background: #ef6461;
                }

                .mayush-toast__title {
                    display: block;
                    color: #fff;
                    font-size: 13px;
                    font-weight: 800;
                }

                .mayush-toast__message {
                    margin: 3px 0 0;
                    color: #cbd5e1;
                    font-size: 12px;
                    line-height: 1.45;
                }

                .mayush-toast__dismiss {
                    width: 28px;
                    height: 28px;
                    padding: 0;
                    color: #cbd5e1;
                    background: transparent;
                    border: 0;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 22px;
                    line-height: 1;
                }

                .mayush-toast__dismiss:hover {
                    color: #fff;
                    background: rgba(255, 255, 255, .1);
                }

                .mayush-toast__dismiss:focus-visible {
                    outline: 3px solid rgba(20, 184, 166, .52);
                    outline-offset: 2px;
                }

                @media (prefers-reduced-motion: reduce) {
                    .mayush-toast {
                        scroll-behavior: auto;
                    }
                }
            </style>
        @endonce
        <meta name="notification-center"
              data-user-id="{{ auth()->id() }}"
              data-summary-url="{{ route('notifications.summary') }}"
              data-read-url="{{ route('notifications.read', '__ID__') }}"
              data-ack-url="{{ route('notifications.broadcast-ack', '__ID__') }}"
              data-priority-info-label="{{ app()->getLocale() === 'fr' ? 'Information' : translate('Info') }}"
              data-priority-important-label="{{ app()->getLocale() === 'fr' ? 'Important' : translate('Important') }}"
              data-priority-critical-label="{{ app()->getLocale() === 'fr' ? 'Critique' : translate('Critical') }}"
              data-status-read-label="{{ app()->getLocale() === 'fr' ? 'Lu' : translate('Read') }}"
              data-status-unread-label="{{ app()->getLocale() === 'fr' ? 'Non lu' : translate('Unread') }}"
              data-broadcasting="{{ config('notifications_v2.broadcasting_enabled') ? '1' : '0' }}"
              data-key="{{ config('broadcasting.connections.reverb.key') }}"
              data-host="{{ config('broadcasting.connections.reverb.options.host') }}"
              data-port="{{ config('broadcasting.connections.reverb.options.port', 443) }}"
              data-scheme="{{ config('broadcasting.connections.reverb.options.scheme', 'https') }}">
        <script type="module" src="{{ storefront_asset('notifications.js') }}"></script>
    @endif
@endauth
