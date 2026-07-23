<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#111827">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:28px">
            <h1 style="font-size:22px;margin:0 0 16px">{{ $payload['title'] }}</h1>
            <p style="font-size:16px;line-height:1.6;margin:0 0 24px">{{ $payload['message'] }}</p>
            @if(!empty($payload['action_url']))
                <a href="{{ $payload['action_url'] }}" style="display:inline-block;background:#0f766e;color:#fff;text-decoration:none;padding:12px 20px;border-radius:8px">
                    {{ translate('View details') }}
                </a>
            @endif
        </div>
    </div>
</body>
</html>
