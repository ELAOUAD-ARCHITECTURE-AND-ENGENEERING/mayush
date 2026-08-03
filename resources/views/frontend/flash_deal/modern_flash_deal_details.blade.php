@extends('frontend.layouts.app')

@section('content')
<!-- Google Fonts for Reference Fidelity -->
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/modern.css') }}?v={{ time() }}">

<div id="modern-flash-deals">
    <!-- HERO SECTION (Specific to this Deal) -->
    <section class="deals-hero">
      <div class="hero-inner">
        <div class="hero-left">
          <div class="hero-badge">
            <span class="live-dot"></span> {{ translate('Actif Maintenant') }} · {{ $flash_deal->title }}
          </div>
          <h1 class="hero-title">{{ translate('OFFRE') }}<br><span class="hollow">{{ translate('EXCLUSIVE') }}</span></h1>
          <p class="hero-sub">{{ $flash_deal->title }} — {{ translate('Profitez de réductions exceptionnelles sur cette sélection.') }}</p>
        </div>
        
        <div class="main-timer" id="main-countdown" data-end="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}">
          <span class="timer-label">{{ translate('Expire Dans') }}</span>
          <div class="t-block"><span class="t-num" id="mt-d">--</span><div class="t-unit">{{ translate('JOURS') }}</div></div>
          <span class="t-sep">:</span>
          <div class="t-block"><span class="t-num" id="mt-h">--</span><div class="t-unit">{{ translate('HEU') }}</div></div>
          <span class="t-sep">:</span>
          <div class="t-block"><span class="t-num" id="mt-m">--</span><div class="t-unit">{{ translate('MIN') }}</div></div>
          <span class="t-sep">:</span>
          <div class="t-block"><span class="t-num" id="mt-s">--</span><div class="t-unit">{{ translate('SEC') }}</div></div>
        </div>
      </div>
    </section>

    <!-- CONTROLS BAR (Cross-Navigation) -->
    <div class="controls-bar" style="margin-bottom: 32px">
      <div class="pills">
        <a href="{{ route('flash-deals') }}" class="pill">{{ translate('All Deals') }}</a>
        @foreach($all_flash_deals as $pill_deal)
            <a href="{{ route('flash-deal-details', $pill_deal->slug) }}" class="pill {{ $pill_deal->id == $flash_deal->id ? 'active' : '' }}">
                <span>⚡</span> {{ $pill_deal->getTranslation('title') }}
            </a>
        @endforeach
      </div>
    </div>

    <!-- PRODUCT GRID (Deal Specific) -->
    <div class="grid-section">
      <div class="section-hdr" style="margin-bottom:24px; padding-left: max(16px, env(safe-area-inset-left));">
        <h2 class="section-title" style="font-family: 'Outfit', 'Inter', -apple-system, sans-serif; font-size: clamp(22px, 3.5vw, 32px); font-weight: 700; letter-spacing: -0.02em;">
          {{ translate('Produits de') }} <span style="color: var(--fire);">{{ $flash_deal->getTranslation('title') }}</span>
        </h2>
        <span style="font-size:12px;color:var(--ink-3)">{{ count($flash_deal->flash_deal_products) }} {{ translate('articles exclusifs') }}</span>
      </div>
      
      <div class="products-grid">
        @include('frontend.flash_deal.partials.single_deal_product_grid', ['flash_deal' => $flash_deal])
      </div>
    </div>


</div>

@endsection

@section('script')
<script>
    'use strict';
    function updateCountdowns() {
        document.querySelectorAll('[data-end]').forEach(el => {
            const endDate = new Date(el.dataset.end).getTime();
            const now = new Date().getTime();
            const diff = endDate - now;
            if (diff <= 0) return;
            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);
            const dStr = d.toString().padStart(2, '0');
            const hStr = h.toString().padStart(2, '0');
            const mStr = m.toString().padStart(2, '0');
            const sStr = s.toString().padStart(2, '0');
            if (el.id === 'main-countdown') {
                const dEl = document.getElementById('mt-d');
                if (dEl) dEl.textContent = dStr;
                const hEl = document.getElementById('mt-h');
                if (hEl) hEl.textContent = hStr;
                const mEl = document.getElementById('mt-m');
                if (mEl) mEl.textContent = mStr;
                const sEl = document.getElementById('mt-s');
                if (sEl) sEl.textContent = sStr;
            } else if (el.classList.contains('mini-timer')) {
                const dEl = el.querySelector('.mt-d');
                if (dEl) dEl.textContent = dStr;
                const hEl = el.querySelector('.mt-h');
                if (hEl) hEl.textContent = hStr;
                const mEl = el.querySelector('.mt-m');
                if (mEl) mEl.textContent = mStr;
                const sEl = el.querySelector('.mt-s');
                if (sEl) sEl.textContent = sStr;
            }
        });
    }
    setInterval(updateCountdowns, 1000);
    updateCountdowns();

    // ── 60 s AJAX Auto-Refresh (lightweight, no full page reload) ──
    const REFRESH_URL      = "{{ route('flash-deal-details-grid', $flash_deal->slug) }}";
    const REFRESH_INTERVAL = 60000; // 60 seconds
    let   refreshTimer     = null;
    let   isRefreshing     = false;

    async function refreshGrid() {
        if (isRefreshing) return;
        if (document.hidden) return;
        isRefreshing = true;

        try {
            const resp = await fetch(REFRESH_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) throw new Error('Network ' + resp.status);
            const html = await resp.text();

            const grid = document.querySelector('.products-grid');
            if (grid) {
                grid.innerHTML = html;
                updateCountdowns(); // refresh timers on new cards
            }
        } catch (e) {
            console.warn('[Flash Deal Details] Auto-refresh failed:', e.message);
        } finally {
            isRefreshing = false;
        }
    }

    function startAutoRefresh() {
        if (refreshTimer) return;
        refreshTimer = setInterval(refreshGrid, REFRESH_INTERVAL);
    }
    function stopAutoRefresh() {
        if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
    }

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAutoRefresh() : startAutoRefresh();
    });

    startAutoRefresh();
</script>
@endsection
