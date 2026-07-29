{{-- Overview Tab --}}
@if($activeTab === 'Overview')
<div class="row gutters-10">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Revenue Analytics') }}</h6>
                <p class="text-muted mb-3" style="font-size:11px;">{{ translate('Historical sales trend') }}</p>
                <div id="revenueChartEl" style="min-height:320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Traffic Sources') }}</h6>
                <p class="text-muted mb-3" style="font-size:11px;">{{ translate('Session distribution') }}</p>
                <div id="trafficChartEl" style="min-height:320px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Finance Tab --}}
@elseif($activeTab === 'Finance')
<div class="row gutters-10">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0" style="font-size:15px;">{{ translate('Recent Vendor Payouts') }}</h6>
                        <span class="text-muted" style="font-size:11px;">{{ translate('Awaiting reconciliation') }}</span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr class="text-uppercase" style="font-size:10px;letter-spacing:.05em;">
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Vendor') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Amount') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Status') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Date') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse($payouts as $p)
                            <tr>
                                <td class="fw-semibold" style="font-size:12px;">{{ $p['vendor'] ?? translate('Unknown') }}</td>
                                <td style="font-size:12px;color:var(--primary);">${{ number_format($p['amount'] ?? 0, 2) }}</td>
                                <td><span class="badge {{ ($p['status'] ?? '') === 'Paid' ? 'badge-soft-success' : (($p['status'] ?? '') === 'Pending' ? 'badge-soft-warning' : 'badge-soft-info') }}" style="font-size:10px;">{{ translate($p['status'] ?? 'N/A') }}</span></td>
                                <td class="text-muted" style="font-size:11px;">{{ $p['date'] ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:12px;">{{ translate('No recent payouts found.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                        <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Refund Trend') }}</h6>
                        <p class="text-muted mb-3" style="font-size:11px;">{{ translate('Quality score timeline') }}</p>
                <div id="refundChartEl" style="min-height:260px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Marketing Tab --}}
@elseif($activeTab === 'Marketing')
<div class="row gutters-10">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                        <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Active Campaigns') }}</h6>
                        <p class="text-muted mb-3" style="font-size:11px;">{{ translate('ROI tracking via coupon attribution') }}</p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr class="text-uppercase" style="font-size:10px;letter-spacing:.05em;">
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Campaign') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Status') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse($marketingMetrics['campaigns'] ?? [] as $c)
                            <tr>
                                <td class="fw-semibold" style="font-size:12px;">{{ $c['name'] ?? translate('Unknown') }}</td>
                            <td><span class="badge {{ ($c['status'] ?? '') === 'Live' ? 'badge-soft-success' : 'badge-soft-secondary' }}" style="font-size:10px;">{{ translate($c['status'] ?? 'N/A') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-4" style="font-size:12px;">{{ translate('No campaigns found.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                        <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Acquisition') }}</h6>
                        <p class="text-muted mb-3" style="font-size:11px;">{{ translate('Marketing funnel') }}</p>
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1" style="font-size:11px;"><span class="text-muted">{{ translate('Campaign Visits') }}</span><span class="fw-bold" style="color:var(--primary);">{{ number_format($marketingMetrics['campaign_visits'] ?? 0) }}</span></div>
                    <div class="progress" style="height:6px;"><div class="progress-bar" style="width:65%;background:var(--primary);"></div></div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:11px;"><span class="text-muted">{{ translate('Coupon Revenue') }}</span><span class="fw-bold text-success">${{ number_format($marketingMetrics['coupon_revenue'] ?? 0, 2) }}</span></div>
                    <div class="progress" style="height:6px;"><div class="progress-bar bg-success" style="width:42%;"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Security Tab --}}
@elseif($activeTab === 'Security')
<div class="row gutters-10">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body">
                        <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Security Audit Logs') }}</h6>
                        <p class="text-muted mb-3" style="font-size:11px;">{{ translate('Administrative actions') }}</p>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr class="text-uppercase" style="font-size:10px;letter-spacing:.05em;">
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Time') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Admin') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('Action') }}</th>
                            <th class="text-muted fw-bold border-0 pb-2">{{ translate('IP') }}</th>
                        </tr></thead>
                        <tbody>
                        @forelse($securityMetrics['recent_events'] ?? [] as $ev)
                            <tr>
                                <td class="text-muted" style="font-size:10px;font-family:monospace;">{{ \Carbon\Carbon::parse($ev['created_at'] ?? now())->format('H:i:s d/m') }}</td>
                                <td class="fw-semibold" style="font-size:12px;">{{ $ev['admin']['name'] ?? translate('System') }}</td>
                                <td style="font-size:12px;">{{ translate($ev['action_type'] ?? '') }}<br><small class="text-muted">{{ $ev['description'] ?? '' }}</small></td>
                                <td style="font-size:11px;color:var(--primary);">{{ $ev['ip_address'] ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4" style="font-size:12px;">{{ translate('No security events logged.') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card shadow-sm border-0 rounded-2 mb-3 border-start border-danger border-3">
            <div class="card-body text-center">
                <div class="text-uppercase fw-bold text-danger" style="font-size:10px;letter-spacing:.1em;">{{ translate('Auth Failures') }}</div>
                <div class="fs-3 fw-bold mt-1">{{ number_format($securityMetrics['failed_logins'] ?? 0) }}</div>
                <div class="text-muted" style="font-size:10px;">{{ translate('Blocked attempts') }}</div>
            </div>
        </div>
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body text-center">
                <div class="text-uppercase fw-bold" style="font-size:10px;letter-spacing:.1em;color:var(--primary);">{{ translate('Blocked Uploads') }}</div>
                <div class="fs-3 fw-bold mt-1">{{ number_format($securityMetrics['blocked_uploads'] ?? 0) }}</div>
                <div class="text-muted" style="font-size:10px;">{{ translate('Sanitization events') }}</div>
            </div>
        </div>
        <div class="card shadow-sm border-0 rounded-2 mb-3">
            <div class="card-body text-center">
                <div class="text-uppercase fw-bold" style="font-size:10px;letter-spacing:.1em;color:var(--primary);">{{ translate('System Health') }}</div>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                    <span class="rounded-circle bg-success d-inline-block" style="width:8px;height:8px;"></span>
                    <span class="fw-bold text-success" style="font-size:12px;">{{ translate('Nominal') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Vendors Tab --}}
@elseif($activeTab === 'Vendors')
<div class="card shadow-sm border-0 rounded-2 mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-1" style="font-size:15px;">{{ translate('Top Performance Leaders') }}</h6>
        <p class="text-muted mb-3" style="font-size:11px;">{{ translate("Yesterday's snapshot") }}</p>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr class="text-uppercase" style="font-size:10px;letter-spacing:.05em;">
                    <th class="text-muted fw-bold border-0 pb-2">#</th>
                    <th class="text-muted fw-bold border-0 pb-2">{{ translate('Vendor') }}</th>
                    <th class="text-muted fw-bold border-0 pb-2 text-center">{{ translate('Rating') }}</th>
                    <th class="text-muted fw-bold border-0 pb-2 text-end">{{ translate('Orders') }}</th>
                    <th class="text-muted fw-bold border-0 pb-2 text-end">{{ translate('Revenue') }}</th>
                </tr></thead>
                <tbody>
                @forelse($vendorDirectory as $idx => $v)
                    <tr>
                        <td><span class="badge {{ $idx == 0 ? 'bg-warning text-dark' : ($idx == 1 ? 'bg-secondary' : ($idx == 2 ? 'bg-dark' : 'bg-light text-dark')) }}" style="font-size:10px;">{{ $idx + 1 }}</span></td>
                        <td>
                            <span class="fw-semibold" style="font-size:12px;">{{ $v['seller']['shop']['name'] ?? ($v['seller']['name'] ?? translate('Partner').' #'.($v['seller_id'] ?? '?')) }}</span>
                            <br><small class="text-muted">{{ translate('ID') }}: #VN-{{ str_pad($v['seller_id'] ?? 0, 4, '0', STR_PAD_LEFT) }}</small>
                        </td>
                        <td class="text-center"><span class="text-warning" style="font-size:11px;">★</span> <span style="font-size:11px;">{{ number_format($v['avg_rating'] ?? 0, 1) }}</span></td>
                        <td class="text-end fw-semibold" style="font-size:12px;">{{ number_format($v['total_orders'] ?? $v['orders_count'] ?? 0) }}</td>
                        <td class="text-end fw-bold" style="font-size:12px;color:var(--primary);">${{ number_format($v['total_revenue'] ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4" style="font-size:12px;">{{ translate('No vendor data available.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
