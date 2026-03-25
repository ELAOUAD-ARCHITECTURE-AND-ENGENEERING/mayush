@extends('backend.layouts.app')

@section('styles')
<style>
    .bg-gradient-danger {
        background: linear-gradient(135deg, #f5365c 0, #f56036 100%) !important;
        box-shadow: 0 4px 20px 0 rgba(0,0,0,.14), 0 7px 10px -5px rgba(244,67,54,.4);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #fb6340 0, #fbb140 100%) !important;
        box-shadow: 0 4px 20px 0 rgba(0,0,0,.14), 0 7px 10px -5px rgba(255,152,0,.4);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #2dce89 0, #2dcecc 100%) !important;
        box-shadow: 0 4px 20px 0 rgba(0,0,0,.14), 0 7px 10px -5px rgba(76,175,80,.4);
    }
    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .fs-32 { font-size: 2rem; }
    .fw-700 { font-weight: 700; }
    .opacity-60 { opacity: 0.6; }
    .opacity-40 { opacity: 0.4; }
</style>
@endsection

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Security Overview') }}</h1>
        </div>
    </div>
</div>

<div class="row gutters-10">
    <div class="col-md-4">
        <div class="card bg-gradient-danger text-white overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h2 class="fs-32 fw-700 mb-1" id="failed-logins">-</h2>
                        <div class="fs-14 opacity-60">{{ translate('Failed Logins (24h)') }}</div>
                    </div>
                    <div class="opacity-40">
                        <i class="las la-unlock-alt la-4x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-gradient-warning text-white overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h2 class="fs-32 fw-700 mb-1" id="blocked-uploads">-</h2>
                        <div class="fs-14 opacity-60">{{ translate('Blocked Malware (24h)') }}</div>
                    </div>
                    <div class="opacity-40">
                        <i class="las la-shield-alt la-4x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-gradient-success text-white overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h2 class="fs-32 fw-700 mb-1 text-uppercase" id="system-health">SECURE</h2>
                        <div class="fs-14 opacity-60">{{ translate('Overall System Health') }}</div>
                    </div>
                    <div class="opacity-40">
                        <i class="las la-check-circle la-4x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Recent Security Events') }}</h5>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Time') }}</th>
                            <th>{{ translate('Event') }}</th>
                            <th>{{ translate('IP Address') }}</th>
                            <th>{{ translate('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody id="security-event-logs">
                        <!-- Content loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    function loadSecurityMetrics() {
        $.get('{{ route('api.analytics.security_metrics') }}', function(data) {
            $('#failed-logins').text(data.failed_logins_24h);
            $('#blocked-uploads').text(data.blocked_uploads_24h);
            $('#system-health').text(data.system_health.toUpperCase());
            
            // Dynamic health card coloring
            let healthCard = $('#system-health').closest('.card');
            healthCard.removeClass('bg-gradient-success bg-gradient-warning bg-gradient-danger');
            if(data.system_health === 'secure') healthCard.addClass('bg-gradient-success');
            else if(data.system_health === 'warning') healthCard.addClass('bg-gradient-warning');
            else healthCard.addClass('bg-gradient-danger');

            // Populate security event logs
            let eventsHtml = '';
            if(data.recent_events && data.recent_events.length > 0) {
                data.recent_events.forEach(function(ev) {
                    let badgeClass = 'badge-inline badge-soft-primary';
                    if(ev.event === 'FAILED_LOGIN' || ev.event === 'MALWARE_BLOCKED' || ev.event === 'UNAUTHORIZED_ACCESS') {
                        badgeClass = 'badge-inline badge-soft-danger';
                    }
                    
                    eventsHtml += `<tr>
                        <td>${ev.time}</td>
                        <td><span class="badge ${badgeClass}">${ev.event}</span></td>
                        <td>${ev.ip}</td>
                        <td class="text-truncate" style="max-width: 300px;">${ev.description}</td>
                    </tr>`;
                });
            } else {
                eventsHtml = '<tr><td colspan="4" class="text-center text-muted">{{ translate('No recent security events detected') }}</td></tr>';
            }
            $('#security-event-logs').html(eventsHtml);
        });
    }

    $(document).ready(function() {
        loadSecurityMetrics();
        setInterval(loadSecurityMetrics, 30000); // Update every 30 seconds for better responsiveness
    });
</script>
@endsection
