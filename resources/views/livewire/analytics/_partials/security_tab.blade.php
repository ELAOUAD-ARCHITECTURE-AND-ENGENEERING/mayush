@php $sm = $securityMetrics ?? []; @endphp

{{-- SECURITY KPIs --}}
<div class="td-kpi-grid">
    @php $kpis = [
        ['label'=>'System Health','value'=>$sm['system_health']??'Secure','icon'=>'🛡️','delta'=>null,'up'=>($sm['system_health']??'')==='secure'],
        ['label'=>'Failed Logins (24h)','value'=>$sm['failed_logins']??0,'icon'=>'🔐','delta'=>null,'up'=>($sm['failed_logins']??0)==0],
        ['label'=>'Blocked Uploads (24h)','value'=>$sm['blocked_uploads']??0,'icon'=>'🚫','delta'=>null,'up'=>($sm['blocked_uploads']??0)==0],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="td-kpi">
        <div class="td-kpi-icon">{{ $k['icon'] }}</div>
        <div class="td-kpi-label">{{ $k['label'] }}</div>
        <div class="td-kpi-value" style="color:{{ $k['up'] ? '#10b981' : '#ef4444' }}">{{ $k['value'] }}</div>
    </div>
    @endforeach
</div>

{{-- SECURITY EVENTS TABLE --}}
<div class="td-card">
    <div class="td-card-title">Recent Security Events</div>
    <div class="td-card-sub">Audit logs for the last 24 hours</div>
    <table class="td-table">
        <thead><tr><th>Time</th><th>Admin/User</th><th>Action Type</th><th>Description</th><th>IP Address</th></tr></thead>
        <tbody>
        @forelse($sm['recent_events'] ?? [] as $e)
        <tr>
            <td style="color:#64748b">{{ \Carbon\Carbon::parse($e['created_at'])->diffForHumans() }}</td>
            <td style="font-weight:600">{{ $e['admin']['name'] ?? 'System' }}</td>
            <td>
                <span class="td-badge {{ str_contains($e['action_type'], 'FAIL') || str_contains($e['action_type'], 'BLOCK') ? 'td-badge-err' : 'td-badge-info' }}">
                    {{ $e['action_type'] }}
                </span>
            </td>
            <td>{{ $e['description'] }}</td>
            <td style="font-family:monospace;color:#6366f1">{{ $e['ip_address'] }}</td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:15px">No recent security events</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
