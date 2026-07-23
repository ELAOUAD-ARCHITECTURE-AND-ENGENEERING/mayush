@auth
    @if (config('notifications_v2.enabled') && storefront_asset('notifications.js'))
        <meta name="notification-center"
              data-user-id="{{ auth()->id() }}"
              data-summary-url="{{ route('notifications.summary') }}"
              data-ack-url="{{ route('notifications.broadcast-ack', '__ID__') }}"
              data-broadcasting="{{ config('notifications_v2.broadcasting_enabled') ? '1' : '0' }}"
              data-key="{{ config('broadcasting.connections.reverb.key') }}"
              data-host="{{ config('broadcasting.connections.reverb.options.host') }}"
              data-port="{{ config('broadcasting.connections.reverb.options.port', 443) }}"
              data-scheme="{{ config('broadcasting.connections.reverb.options.scheme', 'https') }}">
        <script type="module" src="{{ storefront_asset('notifications.js') }}"></script>
    @endif
@endauth
