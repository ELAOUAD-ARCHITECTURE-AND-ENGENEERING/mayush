@php
    $flash_deal = get_featured_flash_deal();
    $is_valid_deal = false;
    if($flash_deal != null && $flash_deal->is_active){
        $flash_deal_products = get_flash_deal_products($flash_deal->id);
        if(count($flash_deal_products) > 0){
            $is_valid_deal = true;
        }
    }
@endphp

@if ($is_valid_deal)
    <style>
        .cyber-monday-flash {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.2);
        }
        .flash-deal-banner-content {
            background-size: cover;
            background-position: center;
            min-height: 480px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: white;
            text-align: center;
        }
        .flash-deal-banner-content h2,
        .flash-deal-banner-content h2 * {
            color: #ffffff !important;
            font-size: 3rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1rem;
            text-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        /* Premium Pill Design */
        .premium-pill {
            display: inline-block;
            background: rgba(255, 193, 7, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 193, 7, 0.5);
            padding: 6px 20px;
            border-radius: 50px;
            color: #ffc107;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 0 15px rgba(255, 193, 7, 0.3);
            animation: pulse-glow 2s infinite ease-in-out;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 10px rgba(255, 193, 7, 0.3); transform: scale(1); }
            50% { box-shadow: 0 0 20px rgba(255, 193, 7, 0.6); transform: scale(1.03); }
        }

        /* Fix for Product Box Hover in Flash Deals */
        #flash_deal .carousel-box {
            position: relative;
            z-index: 10;
        }
        #flash_deal .aiz-card-box {
            padding-bottom: 0 !important;
            transition: all 0.3s ease;
            position: relative;
            height: 100%;
            border: none !important;
        }
        
        /* Premium Discount Tag Overrides */
        #flash_deal .absolute-top-left {
            background: #ef4444 !important;
            color: #fff !important;
            min-width: 50px !important;
            height: 24px !important;
            border-radius: 4px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3) !important;
            left: 10px !important;
            top: 10px !important;
            padding: 0 8px !important;
            z-index: 5;
        }

        /* Wishlist/Compare Floating Circles */
        #flash_deal .aiz-p-hov-icon.absolute-top-right {
            display: flex !important;
            flex-direction: column !important;
            gap: 8px !important;
            top: 10px !important;
            right: -50px !important; /* Start hidden */
            left: auto !important;
            background: transparent !important;
            padding: 0 !important;
            transition: all 0.3s ease-in-out !important;
        }
        #flash_deal .aiz-card-box:hover .aiz-p-hov-icon.absolute-top-right {
            right: 10px !important;
        }
        #flash_deal .aiz-p-hov-icon a {
            width: 35px !important;
            height: 35px !important;
            background: #fff !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            margin: 0 !important;
            transition: all 0.2s ease !important;
        }
        #flash_deal .aiz-p-hov-icon a:hover {
            background: #ef4444 !important;
        }
        #flash_deal .aiz-p-hov-icon a:hover svg path {
            fill: #fff !important;
        }

        /* Refined Cart Overlay Button */
        #flash_deal .aiz-card-box .cart-btn {
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            bottom: auto !important;
            right: auto !important;
            width: 65% !important; /* Smaller, more refined */
            transform: translate(-50%, -50%) scale(0.8) !important;
            border-radius: 50px !important;
            background: #ef4444 !important;
            height: 48px !important;
            opacity: 0 !important;
            visibility: hidden !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.4) !important;
            transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1) !important;
            z-index: 20;
            border: none !important;
        }
        #flash_deal .aiz-card-box:hover .cart-btn {
            opacity: 1 !important;
            visibility: visible !important;
            transform: translate(-50%, -50%) scale(1) !important;
        }
        #flash_deal .cart-btn-text {
            font-size: 13px !important;
            font-weight: 700 !important;
        }

        /* Price Vertical Stack Fix (Prevents Overlap) */
        #flash_deal .fs-14.d-flex.justify-content-center {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: flex-start !important;
            gap: 0 !important;
            line-height: 1.2 !important;
        }
        #flash_deal .disc-amount {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            margin-bottom: 2px !important;
        }
        #flash_deal .disc-amount del {
            font-size: 11px !important;
            color: #aeaeae !important;
            font-weight: 400 !important;
        }
        #flash_deal .text-primary {
            font-size: 15px !important;
            font-weight: 800 !important;
            color: #ef4444 !important;
            display: block !important;
        }

        #flash_deal .aiz-card-box .p-2 {
            min-height: 110px !important;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 15px 10px !important;
            background: #fff;
            position: relative;
            z-index: 1;
        }
    </style>
    
    <section class="mb-4 mt-4" id="flash_deal">
        <div class="container">
            <div class="cyber-monday-flash row no-gutters align-items-stretch">
                <!-- Left Column: Banner & Timer -->
                <div class="col-xl-4 col-lg-5">
                    <div class="flash-deal-banner-content h-100" style="background-image: linear-gradient(rgba(239, 68, 68, 0.6), rgba(153, 27, 27, 0.8)), url('{{ uploaded_asset($flash_deal->banner) }}')">
                        <div class="z-1 w-100">
                            <div class="premium-pill mb-4">
                                {{ translate('Limited Time Offer') }}
                            </div>
                            <h2>{{ $flash_deal->getTranslation('title') }}</h2>
                            <div class="aiz-count-down-circle mt-4" end-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}" data-circle-color="#fff"></div>
                            <div class="mt-5">
                                <a href="{{ route('flash-deal-details', $flash_deal->slug) }}" class="btn btn-outline-white btn-lg rounded-pill px-5 fw-700 hov-shadow-lg has-transition">
                                    {{ translate('View All') }} <i class="las la-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Product Carousel -->
                <div class="col-xl-8 col-lg-7 bg-white p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                        <h3 class="fs-22 fw-700 mb-0 text-dark">{{ translate('Cyber Deals') }}</h3>
                        <div class="text-muted fs-13">{{ count($flash_deal_products) }} {{ translate('Products Available') }}</div>
                    </div>
                    <div class="aiz-carousel gutters-10 arrow-dark arrow-inactive-none" 
                        data-items="4" data-xxl-items="4" data-xl-items="3" data-lg-items="2" 
                        data-md-items="2" data-sm-items="2" data-xs-items="2" 
                        data-arrows='true' data-infinite='true' data-autoplay='true' data-rows="2">
                        @foreach ($flash_deal_products as $key => $flash_deal_product)
                            @if ($flash_deal_product->product != null)
                                <div class="carousel-box border rounded hov-shadow-md has-transition mb-2 bg-white overflow-hidden">
                                    @include('frontend.metro.partials.product_box_2', ['product' => $flash_deal_product->product])
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
