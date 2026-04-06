@if (count($todays_deal_products) > 0)
    <style>
        .todays-deal-yellow-section {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(245, 158, 11, 0.2);
            color: #fff;
            text-align: center;
        }
        .todays-deal-yellow-section h2 {
            font-size: 3.5rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: -2px;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .todays-deal-yellow-section .deal-btn {
            background: white;
            color: #f59e0b;
            font-weight: 700;
            padding: 0.75rem 2.5rem;
            border-radius: 50px;
            text-transform: uppercase;
            font-size: 1.2rem;
            margin-top: 2rem;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .todays-deal-item-circle {
            background: white;
            border-radius: 50%;
            width: 140px;
            height: 140px;
            margin: 0 auto;
            position: relative;
            padding: 1rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .todays-deal-illustration {
            width: 300px;
            height: 300px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 0;
            opacity: 0.1;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }
        .todays-deal-illustration.left { left: -5%; }
        .todays-deal-illustration.right { right: -5%; }
    </style>

    <section class="mb-4 mt-5">
        <div class="container">
            <div class="todays-deal-yellow-section has-transition hov-shadow-lg p-5">
                <!-- Premium CSS Decorative Elements -->
                <div class="todays-deal-illustration left d-none d-xl-block"></div>
                <div class="todays-deal-illustration right d-none d-xl-block"></div>
                
                <div class="z-1 w-100">
                    <h2 class="mb-0 text-white">{{ translate('Specially Made For U') }}</h2>
                    <div class="btn deal-btn mb-5">{{ translate('Today\'s Deal') }}</div>
                    <p class="fs-18 mb-5 fw-600 opacity-90">{{ translate('EXCLUSIVE DISCOUNTS ON TODAY\'S SELECTIONS ONLY') }}</p>

                    <div class="aiz-carousel gutters-16 dots-white" 
                        data-items="6" data-xl-items="5" data-lg-items="4" data-md-items="3" 
                        data-sm-items="2" data-xs-items="2" data-autoplay='true' data-infinite='true' data-dots="true" data-arrows="true">
                        @foreach ($todays_deal_products as $key => $product)
                            <div class="carousel-box">
                                <a href="{{ route('product', $product->slug) }}" class="d-block text-reset">
                                    <div class="todays-deal-item-circle hov-scale-img has-transition">
                                        <img src="{{ get_image($product->thumbnail, 'medium') }}" class="img-fit h-100 w-100 rounded-circle" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                    </div>
                                    <h4 class="fs-15 fw-700 text-white mt-3 text-truncate-2 px-3">{{ $product->getTranslation('name') }}</h4>
                                    <div class="fs-16 fw-900 text-dark-50 mt-1">{{ home_discounted_base_price($product) }}</div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif