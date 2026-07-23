@extends('frontend.layouts.app')

@section('content')
<section class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4" style="gap: 1rem;">
            <div>
                <h1 class="h3 mb-1">{{ translate('Notification preferences') }}</h1>
                <p class="text-muted mb-0">{{ translate('Choose how Mayush contacts you. Critical inbox alerts stay enabled for your protection.') }}</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">{{ translate('Back') }}</a>
        </div>

        <form id="notification-preferences-form" class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5">{{ translate('Quiet hours') }}</h2>
                <div class="row align-items-end mb-4">
                    <div class="col-md-3 mb-3">
                        <label for="notification-timezone">{{ translate('Timezone') }}</label>
                        <input id="notification-timezone" class="form-control" name="timezone" value="{{ $settings->timezone ?: 'UTC' }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="quiet-start">{{ translate('Start') }}</label>
                        <input id="quiet-start" type="time" class="form-control" name="quiet_hours_start" value="{{ $settings->quiet_hours_start }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="quiet-end">{{ translate('End') }}</label>
                        <input id="quiet-end" type="time" class="form-control" name="quiet_hours_end" value="{{ $settings->quiet_hours_end }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="aiz-switch aiz-switch-success mb-0">
                            <input type="checkbox" name="quiet_hours_enabled" @checked($settings->quiet_hours_enabled)>
                            <span class="slider round"></span>
                            <span class="ml-2">{{ translate('Enable quiet hours') }}</span>
                        </label>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th scope="col">{{ translate('Event') }}</th>
                                <th scope="col">{{ translate('Inbox') }}</th>
                                <th scope="col">{{ translate('Live') }}</th>
                                <th scope="col">{{ translate('Email') }}</th>
                                <th scope="col">{{ translate('SMS') }}</th>
                                <th scope="col">{{ translate('Push') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($events as $event)
                            @php $values = $event['channels'] ?: (object) $event['defaults']; @endphp
                            <tr data-preference-event="{{ $event['event_key'] }}">
                                <th scope="row">
                                    <span class="d-block">{{ translate($event['title']) }}</span>
                                    <small class="text-muted">{{ translate(ucfirst($event['category'])) }} · {{ translate(ucfirst($event['severity'])) }}</small>
                                </th>
                                @foreach(['in_app_enabled', 'broadcast_enabled', 'email_enabled', 'sms_enabled', 'push_enabled'] as $channel)
                                    @php
                                        $locked = $channel === 'in_app_enabled' && $event['mandatory_inbox'];
                                        $enabled = $locked || (bool) data_get($values, $channel, false);
                                    @endphp
                                    <td>
                                        <input type="checkbox"
                                               data-preference-channel="{{ $channel }}"
                                               @checked($enabled)
                                               @disabled($locked)
                                               aria-label="{{ translate($event['title']).' '.$channel }}">
                                        @if($locked)
                                            <span class="las la-lock text-muted" title="{{ translate('Required critical inbox alert') }}"></span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex align-items-center justify-content-end mt-3" style="gap: .75rem;">
                    <span id="notification-preferences-status" class="text-muted" role="status" aria-live="polite"></span>
                    <button type="submit" class="btn btn-primary">{{ translate('Save preferences') }}</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@section('script')
<script>
document.getElementById('notification-preferences-form')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const status = document.getElementById('notification-preferences-status');
    const preferences = Array.from(form.querySelectorAll('[data-preference-event]')).map((row) => {
        const value = { event_key: row.dataset.preferenceEvent };
        row.querySelectorAll('[data-preference-channel]').forEach((input) => {
            value[input.dataset.preferenceChannel] = input.disabled ? true : input.checked;
        });
        return value;
    });
    status.textContent = @json(translate('Saving...'));
    const response = await fetch(@json(route('notification-preferences.update')), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            settings: {
                timezone: form.timezone.value,
                quiet_hours_enabled: form.quiet_hours_enabled.checked,
                quiet_hours_start: form.quiet_hours_start.value || null,
                quiet_hours_end: form.quiet_hours_end.value || null,
            },
            preferences,
        }),
    });
    status.textContent = response.ok
        ? @json(translate('Preferences saved'))
        : @json(translate('Unable to save preferences'));
});
</script>
@endsection
