@extends('frontend.layouts.user_panel')

@section('panel_content')
<style>
    /* ===== Phase 4: Loyalty Hub Premium Styles ===== */
    .loyalty-hub-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        border-radius: 16px;
        padding: 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .loyalty-hub-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
        border-radius: 50%;
    }
    .loyalty-hub-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 14px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.15);
    }
    .tier-badge-basic    { background: rgba(100,116,139,0.3); color: #94a3b8; }
    .tier-badge-silver   { background: rgba(148,163,184,0.3); color: #cbd5e1; }
    .tier-badge-gold     { background: rgba(245,158,11,0.25); color: #fcd34d; }
    .tier-badge-platinum { background: rgba(99,102,241,0.25); color: #a5b4fc; }

    .progress-track {
        background: rgba(255,255,255,0.08);
        border-radius: 10px;
        height: 12px;
        overflow: hidden;
        margin: 16px 0;
        position: relative;
    }
    .progress-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .progress-fill::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
        animation: shimmer-loyalty 2.5s infinite;
    }
    @keyframes shimmer-loyalty {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .progress-fill-basic    { background: linear-gradient(90deg, #475569, #64748b); }
    .progress-fill-silver   { background: linear-gradient(90deg, #64748b, #94a3b8); }
    .progress-fill-gold     { background: linear-gradient(90deg, #d97706, #f59e0b); }
    .progress-fill-platinum { background: linear-gradient(90deg, #4f46e5, #6366f1); }

    .stat-card {
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        backdrop-filter: blur(10px);
    }
    .stat-card .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: 12px;
        color: rgba(255,255,255,0.5);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 4px;
    }

    .benefits-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .benefit-row {
        display: flex;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .benefit-row:last-child { border-bottom: none; }
    .benefit-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        margin-right: 16px;
    }
    .benefit-icon-active   { background: #ecfdf5; }
    .benefit-icon-inactive { background: #f8fafc; opacity: 0.5; }

    .history-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
    }
    .history-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .history-row:last-child { border-bottom: none; }
    .history-points-badge {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        color: #059669;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
    }

    .tier-step {
        position: relative;
        text-align: center;
        flex: 1;
    }
    .tier-step-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.15);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        margin-bottom: 6px;
        transition: all 0.3s;
    }
    .tier-step-dot.active {
        border-color: currentColor;
        box-shadow: 0 0 12px rgba(255,255,255,0.15);
    }
    .tier-step-dot.completed {
        background: currentColor;
    }
    .tier-steps-track {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin: 24px 0 8px 0;
    }
    .tier-step-label {
        font-size: 11px;
        opacity: 0.6;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .tier-step-label.active-label { opacity: 1; }

    .loyalty-hub-header .z-index-1 { position: relative; z-index: 1; }
</style>

{{-- ===== LOYALTY HUB HEADER ===== --}}
<div class="loyalty-hub-header" id="loyalty-hub-header">
    <div class="z-index-1">
        {{-- Title Row --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-800 mb-1 fs-20" style="color: #fff;">{{ translate('Loyalty Lounge') }}</h4>
                <p class="mb-0 fs-13" style="color: rgba(255,255,255,0.5);">{{ translate('Your rewards journey at a glance') }}</p>
            </div>
            <span class="tier-badge tier-badge-{{ $tierMeta['key'] }}">
                {{ $tierMeta['icon'] }} {{ translate($tierMeta['label']) }} {{ translate('Member') }}
            </span>
        </div>

        {{-- Stats Row --}}
        <div class="row gutters-10 mb-3">
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($pointBalance) }}</div>
                    <div class="stat-label">{{ translate('Total Points') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    <div class="stat-value">{{ single_price($annualSpend) }}</div>
                    <div class="stat-label">{{ translate('Annual Spend') }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    @php
                        $multiplier = ($tierProgress['current_tier'] && isset($tierProgress['current_tier']->loyalty_multiplier))
                            ? $tierProgress['current_tier']->loyalty_multiplier
                            : 1.0;
                    @endphp
                    <div class="stat-value">{{ $multiplier }}x</div>
                    <div class="stat-label">{{ translate('Point Multiplier') }}</div>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        @if ($tierProgress['next_tier'])
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-12" style="color:rgba(255,255,255,0.6);">
                        {{ translate('Progress to') }}
                        <strong style="color:#fff;">{{ $tierProgress['next_tier']->getTranslation('name') }}</strong>
                    </span>
                    <span class="fs-12 fw-700" style="color:rgba(255,255,255,0.8);">
                        {{ $tierProgress['percent'] }}%
                    </span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill progress-fill-{{ $tierMeta['key'] }}" style="width: {{ $tierProgress['percent'] }}%;"></div>
                </div>
                <p class="fs-11 mb-0" style="color:rgba(255,255,255,0.4);">
                    {{ translate('Spend') }}
                    <strong style="color:rgba(255,255,255,0.7);">{{ single_price($tierProgress['spend_gap']) }}</strong>
                    {{ translate('more to unlock the next tier') }}
                </p>
            </div>
        @else
            <div class="mt-3 text-center" style="color:rgba(255,255,255,0.5);">
                <span class="fs-12">🎉 {{ translate('You have reached the highest loyalty tier!') }}</span>
            </div>
        @endif

        {{-- Tier Steps --}}
        <div class="tier-steps-track">
            @php
                $allTiers = [
                    ['level' => 0, 'key' => 'basic',    'label' => 'Basic',    'icon' => '⭐'],
                    ['level' => 1, 'key' => 'silver',   'label' => 'Silver',   'icon' => '🥈'],
                    ['level' => 2, 'key' => 'gold',     'label' => 'Gold',     'icon' => '🥇'],
                    ['level' => 3, 'key' => 'platinum', 'label' => 'Platinum', 'icon' => '💎'],
                ];
            @endphp
            @foreach ($allTiers as $t)
                @php
                    $isCompleted = $tierLevel >= $t['level'];
                    $isActive    = $tierLevel == $t['level'];
                    $meta = \App\Services\LoyaltyService::getTierMeta($t['level']);
                @endphp
                <div class="tier-step" data-toggle="tooltip" title="{{ translate('Minimum Spend') }}: {{ single_price($t['level'] * 5000) }}">
                    <div class="tier-step-dot {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}"
                         style="color: {{ $meta['color'] }};">
                        @if($isCompleted)
                            <i class="las la-check fs-14" style="color:#fff;"></i>
                        @else
                           <span style="opacity: 0.5;">{{ $t['icon'] }}</span>
                        @endif
                    </div>
                    <div class="tier-step-label {{ $isActive ? 'active-label' : '' }}">{{ translate($t['label']) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ===== QUICK CONVERSION CARD ===== --}}
<div class="card border-0 mb-4 overflow-hidden" style="border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
    <div class="row no-gutters">
        <div class="col-md-5 bg-primary p-4 text-white d-flex flex-column justify-content-center border-right">
            <h5 class="fw-700 mb-2">{{ translate('Convert to Cash') }}</h5>
            <p class="fs-13 opacity-70 mb-4">{{ translate('Turn your loyalty points into wallet balance to spend on your next order.') }}</p>
            <div class="d-flex align-items-center mb-0">
                <div class="h3 fw-800 mb-0 mr-2">{{ (float)get_setting('club_point_convert_rate', 10) }}</div>
                <div class="fs-12 opacity-80" style="line-height: 1.2;">{{ translate('Points') }}<br>{{ translate('= 1 MAD') }}</div>
            </div>
        </div>
        <div class="col-md-7 p-4 bg-white">
            <form action="{{ route('convert_point_into_wallet') }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label class="fs-12 fw-700 text-uppercase text-muted">{{ translate('Points to Convert') }}</label>
                    <div class="input-group input-group-lg">
                        <input type="number" name="points" id="points-to-convert" class="form-control border-right-0" placeholder="0" min="{{ (float)get_setting('club_point_convert_rate', 10) }}" step="1">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white border-left-0 text-primary fw-700">{{ translate('PTS') }}</span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-secondary" id="conversion-preview">
                            {{ translate('You will receive') }}: <span class="fw-700 text-primary">0.00 MAD</span>
                        </small>
                        <small class="text-primary pointer" onclick="document.getElementById('points-to-convert').value = {{ $pointBalance }}; updatePreview();">
                            <u>{{ translate('Convert All') }}</u>
                        </small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block py-3 fw-700" style="border-radius: 12px; transition: all 0.3s;">
                    <i class="las la-exchange-alt mr-2"></i> {{ translate('Redeem Points Now') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('points-to-convert')?.addEventListener('input', updatePreview);
    
    function updatePreview() {
        const points = document.getElementById('points-to-convert').value || 0;
        const rate = {{ (float)get_setting('club_point_convert_rate', 10) }};
        const amount = (points / rate).toFixed(2);
        const preview = document.getElementById('conversion-preview');
        if (preview) {
            preview.innerHTML = `{{ translate('You will receive') }}: <span class="fw-700 text-primary">${amount} MAD</span>`;
        }
    }
</script>

<div class="row gutters-16">
    {{-- ===== TIER BENEFITS ===== --}}
    <div class="col-lg-6 mb-4">
        <div class="benefits-section h-100">
            <h6 class="fw-700 mb-3 text-dark">
                <i class="las la-gift fs-18 text-primary mr-1"></i>
                {{ translate('Your Tier Benefits') }}
            </h6>

            @php
                $benefits = [
                    ['icon' => '🎁', 'title' => translate('Priority Support')',               'min_tier' => 1],
                    ['icon' => '🚀', 'title' => translate('Early Access to Flash Deals')',     'min_tier' => 1],
                    ['icon' => '💰', 'title' => '1.5x Point Multiplier',           'min_tier' => 1],
                    ['icon' => '📦', 'title' => translate('Free Shipping on Orders')',         'min_tier' => 2],
                    ['icon' => '🏷️', 'title' => translate('Exclusive Gold-Only Coupons')',    'min_tier' => 2],
                    ['icon' => '💎', 'title' => '2x Point Multiplier',             'min_tier' => 2],
                    ['icon' => '👑', 'title' => translate('VIP Concierge Service')',           'min_tier' => 3],
                    ['icon' => '🌟', 'title' => translate('Birthday Bonus (500 pts)')',        'min_tier' => 3],
                    ['icon' => '⚡', 'title' => '3x Point Multiplier',            'min_tier' => 3],
                ];
            @endphp

            @foreach ($benefits as $b)
                @php $unlocked = $tierLevel >= $b['min_tier']; @endphp
                <div class="benefit-row">
                    <div class="benefit-icon {{ $unlocked ? 'benefit-icon-active' : 'benefit-icon-inactive' }}">
                        {{ $b['icon'] }}
                    </div>
                    <div class="flex-grow-1">
                        <span class="fw-600 fs-13 {{ $unlocked ? 'text-dark' : 'text-secondary' }}">
                            {{ translate($b['title']) }}
                        </span>
                    </div>
                    @if ($unlocked)
                        <span class="badge bg-soft-success text-success px-2 py-1" style="border-radius:20px;">
                            <i class="las la-check-circle"></i> {{ translate('Unlocked') }}
                        </span>
                    @else
                        @php
                            $tierNames = ['', 'Silver', 'Gold', 'Platinum'];
                            $neededTier = $tierNames[$b['min_tier']] ?? '';
                        @endphp
                        <span class="badge bg-soft-secondary text-secondary px-2 py-1" style="border-radius:20px;">
                            {{ translate($neededTier) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== POINT HISTORY ===== --}}
    <div class="col-lg-6 mb-4">
        <div class="history-section h-100">
            <h6 class="fw-700 mb-3 text-dark">
                <i class="las la-history fs-18 text-primary mr-1"></i>
                {{ translate('Recent Point Activity') }}
            </h6>

            @if ($pointHistory->count() > 0)
                @foreach ($pointHistory as $detail)
                    <div class="history-row">
                        <div>
                            <span class="fw-600 fs-13 text-dark">
                                @if ($detail->product)
                                    {{ \Illuminate\Support\Str::limit($detail->product->getTranslation('name'), 30) }}
                                @else
                                    {{ translate('Product') }} #{{ $detail->product_id }}
                                @endif
                            </span>
                            <br>
                            <span class="fs-11 text-secondary">
                                {{ $detail->created_at ? $detail->created_at->diffForHumans() : '' }}
                            </span>
                        </div>
                        <span class="history-points-badge">
                            +{{ $detail->point ?? 0 }} {{ translate('pts') }}
                        </span>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <img class="mw-100 h-120px opacity-50" src="{{ static_asset('assets/img/nothing.svg') }}" alt="">
                    <p class="text-secondary mt-3 mb-0 fs-13">{{ translate('No point activity yet. Start shopping to earn!') }}</p>
                </div>
            @endif

            @if (addon_is_activated('club_point'))
                <div class="text-center mt-3">
                    <span class="text-muted fs-12">{{ translate('Earn more points by sharing your referral link!') }}</span>
                    <br>
                    <a href="{{ route('profile') }}#referral" class="btn btn-sm btn-link text-primary fw-700 p-0 mt-1">
                        {{ translate('Go to Referrals') }} <i class="las la-external-link-alt ml-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== HOW IT WORKS ===== --}}
<div class="benefits-section">
    <h6 class="fw-700 mb-4 text-dark">
        <i class="las la-lightbulb fs-18 text-warning mr-1"></i>
        {{ translate('How Loyalty Works') }}
    </h6>
    <div class="row text-center">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="fs-30 mb-2">🛍️</div>
            <h6 class="fw-700 fs-13">{{ translate('Shop') }}</h6>
            <p class="fs-12 text-secondary mb-0">{{ translate('Every purchase earns you loyalty points automatically.') }}</p>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="fs-30 mb-2">📈</div>
            <h6 class="fw-700 fs-13">{{ translate('Accumulate') }}</h6>
            <p class="fs-12 text-secondary mb-0">{{ translate('Your annual spend determines your loyalty tier.') }}</p>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="fs-30 mb-2">⬆️</div>
            <h6 class="fw-700 fs-13">{{ translate('Level Up') }}</h6>
            <p class="fs-12 text-secondary mb-0">{{ translate('Higher tiers unlock exclusive perks and multipliers.') }}</p>
        </div>
        <div class="col-md-3">
            <div class="fs-30 mb-2">🎁</div>
            <h6 class="fw-700 fs-13">{{ translate('Redeem') }}</h6>
            <p class="fs-12 text-secondary mb-0">{{ translate('Convert points to wallet balance or use at checkout.') }}</p>
        </div>
    </div>
</div>

@endsection
