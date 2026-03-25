@extends('frontend.layouts.app')

@section('content')
<!-- Google Fonts for Reference Fidelity -->
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/modern.css') }}?v={{ time() }}">


<div id="modern-flash-deals">
    <!-- FLASH DEALS CONTENT STARTS HERE -->

    @if($all_flash_deals->count() > 0)
    <!-- HERO SECTION -->
    <section class="deals-hero">
      <div class="hero-inner">
        <div class="hero-left">
          <div class="hero-badge">
            <span class="live-dot"></span> {{ translate('Live Now') }} · {{ count($all_flash_deals) }} {{ translate('Active Deals') }}
          </div>
          <h1 class="hero-title">{{ translate('FLASH') }}<br><span class="hollow">{{ translate('DEALS') }}</span></h1>
          <p class="hero-sub">{{ translate('Limited-time offers on top Moroccan deco. Once they\'re gone, they\'re gone.') }}</p>
        </div>
        
        @php
            $main_deal = $all_flash_deals->first();
            $main_end_date = $main_deal ? $main_deal->end_date : time();
        @endphp
        
        <div class="main-timer" id="main-countdown" data-end="{{ date('Y/m/d H:i:s', $main_end_date) }}">
          <span class="timer-label">{{ translate('Resets In') }}</span>
          <div class="t-block"><span class="t-num" id="mt-h">--</span><div class="t-unit">{{ translate('HRS') }}</div></div>
          <span class="t-sep">:</span>
          <div class="t-block"><span class="t-num" id="mt-m">--</span><div class="t-unit">{{ translate('MIN') }}</div></div>
          <span class="t-sep">:</span>
          <div class="t-block"><span class="t-num" id="mt-s">--</span><div class="t-unit">{{ translate('SEC') }}</div></div>
        </div>
      </div>
    </section>

    <!-- CAROUSEL SECTION (Active Flash Deals) -->
    <section class="carousel-section" id="carouselSection">
      <div class="section-hdr">
        <h2 class="section-title">{{ translate('Active') }} <span>{{ translate('Flash Deals') }}</span></h2>
        <a href="{{ route('flash-deals') }}" class="see-all-link">{{ translate('See all deals') }} →</a>
      </div>
      <div class="carousel-outer">
        <button class="carousel-nav prev" id="carouselPrevBtn">‹</button>
        <div class="carousel-track-wrap" id="carouselTrackWrap">
          <div class="carousel-track" id="carouselTrack">
            @foreach($all_flash_deals as $deal)
                @php
                    $products_count = count($deal->flash_deal_products);
                @endphp
                <div class="deal-slide fade-in" onclick="window.location='{{ route('flash-deal-details', $deal->slug) }}'">
                    <div class="slide-img">
                      <img src="{{ uploaded_asset($deal->banner) }}" alt="{{ $deal->title }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                      <div class="slide-overlay"></div>
                    </div>
                    <div class="slide-time">
                      <span class="slide-time-dot"></span>
                      <span class="slide-t-countdown" data-end="{{ date('Y/m/d H:i:s', $deal->end_date) }}">00:00:00</span>
                    </div>
                    <div class="slide-body">
                      <span class="slide-tag">{{ strtoupper($deal->title) }}</span>
                      <div class="slide-name">{{ $deal->title }}</div>
                      <div class="slide-meta">
                        <span class="slide-count">{{ $products_count }} {{ translate('products') }}</span>
                        <span class="slide-discount">{{ translate('Up to -70%') }}</span>
                      </div>
                    </div>
                </div>
            @endforeach
          </div>
        </div>
        <button class="carousel-nav next" id="carouselNextBtn">›</button>
      </div>
      <div class="carousel-dots" id="carouselDots"></div>
    </section>

    <!-- CONTROLS BAR -->
    <div class="controls-bar">
      <div class="pills">
        <a href="{{ route('flash-deals') }}" class="pill active">{{ translate('All Deals') }}</a>
        @foreach($all_flash_deals as $pill_deal)
            <a href="{{ route('flash-deal-details', $pill_deal->slug) }}" class="pill">
                <span>⚡</span> {{ $pill_deal->getTranslation('title') }}
            </a>
        @endforeach
      </div>
      <div class="sort-wrap">
        <span class="sort-lbl">{{ translate('Sort by:') }}</span>
        <select class="sort-sel">
          <option value="velocity">{{ translate('Sales Velocity') }}</option>
          <option value="discount">{{ translate('Biggest Discount') }}</option>
          <option value="price_asc">{{ translate('Price: Low to High') }} ↑</option>
          <option value="price_desc">{{ translate('Price: High to Low') }} ↓</option>
        </select>
      </div>
    </div>

    <!-- PRODUCT GRID -->
    <div class="grid-section">
      <div class="section-hdr" style="margin-bottom:16px">
        <h2 class="section-title">{{ translate('Top Picks Across') }} <span>{{ translate('All Deals') }}</span></h2>
        <span style="font-size:12px;color:var(--ink-3)">{{ translate('Auto-refreshes every 60 s') }}</span>
      </div>
      
      <div class="products-grid" id="flash-deals-grid">
        @include('frontend.flash_deal.partials.product_grid', ['all_flash_deals' => $all_flash_deals])
      </div>
    </div>
    @else
    
    <!-- EMPTY STATE -->
    <style>
    .empty-deals-banner {
      background: var(--surface);
      border-radius: 12px;
      padding: 60px 24px;
      text-align: center;
      box-shadow: 0 4px 16px rgba(0,0,0,0.04);
      margin-bottom: 40px;
    }
    .empty-deals-banner h2 {
      font-family: 'Syne', sans-serif;
      font-size: 32px;
      font-weight: 800;
      color: var(--ink-1);
      margin-bottom: 12px;
      margin-top: 20px;
    }
    .empty-deals-banner p {
      font-size: 16px;
      color: var(--ink-3);
      max-width: 500px;
      margin: 0 auto;
    }
    .fallback-title {
      font-family: 'Syne', sans-serif;
      font-size: 24px;
      font-weight: 700;
      color: var(--ink-1);
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .fallback-title::before {
      content: '';
      display: block;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: var(--primary);
    }
    </style>

    <div class="empty-deals-wrapper" style="padding: 40px 24px; max-width: 1440px; margin: 0 auto;">
        <div class="empty-deals-banner fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--ink-3)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.5;">
                <path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <h2>{{ translate('Deals are resting!') }}</h2>
            <p>{{ translate('There are no active flash deals at the moment, but you can still grab amazing items below.') }}</p>
        </div>

        @if(isset($fallback_best_sellers) && $fallback_best_sellers->count() > 0)
        <div class="fallback-section" style="margin-bottom: 60px;">
            <h3 class="fallback-title">{{ translate('Best Selling Products') }}</h3>
            <div class="products-grid">
                @foreach($fallback_best_sellers as $product)
                    <div class="product-card fade-in">
                        @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($fallback_suggested) && $fallback_suggested->count() > 0)
        <div class="fallback-section" style="margin-bottom: 60px;">
            <h3 class="fallback-title">{{ translate('Products You May Like') }}</h3>
            <div class="products-grid">
                @foreach($fallback_suggested as $product)
                    <div class="product-card fade-in">
                        @include('frontend.'.get_setting('homepage_select').'.partials.product_box_1',['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endif


</div>

@endsection

@section('script')
<script>
    'use strict';

    // Countdown Logic
    function updateCountdowns() {
        document.querySelectorAll('[data-end]').forEach(el => {
            const endDate = new Date(el.dataset.end).getTime();
            const now = new Date().getTime();
            const diff = endDate - now;

            if (diff <= 0) return;

            const h = Math.floor(diff / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);

            const hStr = h.toString().padStart(2, '0');
            const mStr = m.toString().padStart(2, '0');
            const sStr = s.toString().padStart(2, '0');

            if (el.id === 'main-countdown') {
                document.getElementById('mt-h').textContent = hStr;
                document.getElementById('mt-m').textContent = mStr;
                document.getElementById('mt-s').textContent = sStr;
            } else if (el.classList.contains('slide-t-countdown')) {
                el.textContent = `${hStr}:${mStr}:${sStr}`;
            } else if (el.classList.contains('mini-timer')) {
                el.querySelector('.mt-h').textContent = hStr;
                el.querySelector('.mt-m').textContent = mStr;
                el.querySelector('.mt-s').textContent = sStr;
            }
        });
    }

    setInterval(updateCountdowns, 1000);
    updateCountdowns();

    // Carousel Logic
    const track = document.getElementById('carouselTrack');
    const prevBtn = document.getElementById('carouselPrevBtn');
    const nextBtn = document.getElementById('carouselNextBtn');
    const dotsContainer = document.getElementById('carouselDots');
    let currentIndex = 0;
    const cards = document.querySelectorAll('.deal-slide');
    const cardWidth = 320; 

    function updateCarousel() {
        const offset = -currentIndex * cardWidth;
        track.style.transform = `translateX(${offset}px)`;
        
        document.querySelectorAll('.carousel-dot').forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentIndex);
        });

        prevBtn.disabled = currentIndex === 0;
        nextBtn.disabled = currentIndex >= cards.length - 1;
    }

    if(cards.length > 0) {
        for(let i=0; i<cards.length; i++) {
            const dot = document.createElement('span');
            dot.classList.add('carousel-dot');
            if(i === 0) dot.classList.add('active');
            dot.onclick = () => { currentIndex = i; updateCarousel(); };
            dotsContainer.appendChild(dot);
        }

        prevBtn.onclick = () => { if(currentIndex > 0) { currentIndex--; updateCarousel(); } };
        nextBtn.onclick = () => { if(currentIndex < cards.length - 1) { currentIndex++; updateCarousel(); } };
        
        updateCarousel();
    }

    // ── Filter & Sort & Auto-Refresh ──────────────────────────

    function initFilterAndSort() {
        const filterButtons = document.querySelectorAll('.pills .pill');
        const productGrid   = document.getElementById('flash-deals-grid');
        let   productCards   = Array.from(productGrid.querySelectorAll('.product-card'));

        // Filtering (Zone 1)
        filterButtons.forEach(btn => {
            btn.onclick = () => {
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.dataset.filter;

                productCards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === filter) {
                        card.style.display = 'block';
                        card.classList.add('fade-in');
                    } else {
                        card.style.display = 'none';
                    }
                });
            };
        });

        // Sorting (Zone 2)
        const sortSelect = document.querySelector('.sort-sel');
        if(sortSelect) {
            sortSelect.onchange = () => {
                const value = sortSelect.value;
                productCards = Array.from(productGrid.querySelectorAll('.product-card'));
                const sorted = [...productCards].sort((a, b) => {
                    if (value === 'price_asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                    if (value === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                    if (value === 'discount')   return parseFloat(b.dataset.discount) - parseFloat(a.dataset.discount);
                    if (value === 'velocity')   return parseFloat(b.dataset.sales) - parseFloat(a.dataset.sales);
                    return 0;
                });
                productGrid.innerHTML = '';
                sorted.forEach(card => productGrid.appendChild(card));
            };
        }
    }

    initFilterAndSort();

    // ── 60 s AJAX Auto-Refresh (lightweight, no full page reload) ──
    const REFRESH_URL      = "{{ route('flash-deals-grid') }}";
    const REFRESH_INTERVAL = 60000; // 60 seconds
    let   refreshTimer     = null;
    let   isRefreshing     = false;

    async function refreshGrid() {
        if (isRefreshing) return;         // prevent overlapping requests
        if (document.hidden) return;      // skip if tab is not visible
        isRefreshing = true;

        try {
            const resp = await fetch(REFRESH_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) throw new Error('Network ' + resp.status);
            const html = await resp.text();

            const grid = document.getElementById('flash-deals-grid');
            if (grid) {
                grid.innerHTML = html;
                // Re-init filters, sort, and mini-timers on the new DOM
                initFilterAndSort();
                updateCountdowns();
            }
        } catch (e) {
            console.warn('[Flash Deals] Auto-refresh skipped:', e.message);
        } finally {
            isRefreshing = false;
        }
    }

    // Start / stop when tab visibility changes (saves bandwidth)
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
