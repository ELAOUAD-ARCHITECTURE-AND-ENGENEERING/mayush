@php
    $lang = get_system_language()->code;
    $coupons = \App\Models\Coupon::where('status', 1)->where('start_date', '<=', strtotime('now'))->where('end_date', '>=', strtotime('now'))->get();
@endphp

@if (count($coupons) > 0)
    <style>
        .metro-coupon-bar {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 3rem 0;
            border-radius: 12px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.4);
            margin-top: 2.5rem;
            margin-bottom: 2.5rem;
        }
        .coupon-bar-icon {
            font-size: 4rem;
            color: #fbbf24;
            opacity: 0.8;
            margin-right: 2rem;
            filter: drop-shadow(0 4px 12px rgba(251, 191, 36, 0.3));
        }
        .coupon-bar-content h3 {
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        .coupon-bar-content p {
            font-size: 1.1rem;
            opacity: 0.7;
            font-weight: 400;
        }
        .coupon-box-metro {
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .coupon-box-metro:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.02);
            border-color: #fbbf24;
        }
    </style>
    
    <section class="mb-4">
        <div class="container">
            <div class="metro-coupon-bar hov-shadow-lg has-transition px-4 px-md-5">
                <div class="row align-items-center">
                    <div class="col-lg-6 d-flex align-items-center mb-4 mb-lg-0">
                        <i class="las la-tags coupon-bar-icon"></i>
                        <div class="coupon-bar-content">
                            <h3 class="text-white">{{ translate('Save Up to 50% with Our Coupons') }}</h3>
                            <p class="mb-0 text-white opacity-70">{{ translate('Get huge discounts by using active Coupons during checkout!') }}</p>
                            <a href="{{ route('pages.coupons') }}" class="btn btn-warning rounded-pill px-4 mt-3 fw-700 hov-shadow-lg has-transition">
                                {{ translate('View All Coupons') }} <i class="las la-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="aiz-carousel gutters-10 arrow-white arrow-inactive-none" 
                            data-items="2" data-xl-items="2" data-lg-items="2" data-md-items="2" data-sm-items="1" data-xs-items="1" data-arrows='true' data-autoplay='true'>
                            @foreach ($coupons as $key => $coupon)
                                <div class="carousel-box">
                                    <div class="coupon-box-metro">
                                        <div class="fs-12 text-uppercase opacity-50 mb-1">{{ translate('Use Code') }}</div>
                                        <div class="fs-18 fw-900 text-warning mb-2 border-dashed border-2 py-1 px-3 rounded d-inline-block">{{ $coupon->code }}</div>
                                        <div class="fs-14 fw-600 opacity-90">{{ translate('Discount') }}: @if($coupon->discount_type == 'percent') {{ $coupon->discount }}% @else {{ single_price($coupon->discount) }} @endif</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
