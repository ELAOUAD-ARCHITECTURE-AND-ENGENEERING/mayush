@if (count($todays_deal_products) > 0)
    <style>
        .todays-deal-yellow-section {
            background: #F5F1E8;
            padding: 44px 22px;
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: none;
            color: #111827;
            text-align: center;
        }
        .todays-deal-yellow-section h2 {
            font-size: 42px;
            line-height: 1.12;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0;
            margin-bottom: 8px;
            color: #111827;
            text-shadow: none;
        }
        .todays-deal-yellow-section .deal-btn {
            background: white;
            color: #f59e0b;
            font-weight: 800;
            padding: 9px 24px;
            border-radius: 50px;
            text-transform: uppercase;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .todays-deal-urgency-copy {
            max-width: 620px;
            margin: 12px auto 0;
            font-size: 16px;
            line-height: 1.55;
            font-weight: 600;
            color: #111827;
        }
        .todays-deal-header-row {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }
        .todays-deal-countdown {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 13px;
            border-radius: 8px;
            background: #ea580c;
            color: #111827;
            box-shadow: 0 8px 20px rgba(124, 45, 18, .24);
        }
        .todays-deal-countdown__unit {
            min-width: 42px;
            text-align: center;
        }
        .todays-deal-countdown__value {
            display: block;
            font-size: 20px;
            line-height: 1;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
        }
        .todays-deal-countdown__label {
            display: block;
            margin-top: 4px;
            font-size: 10px;
            line-height: 1;
            font-weight: 800;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .86);
        }
        .todays-deal-countdown__separator {
            margin-top: -12px;
            font-size: 20px;
            font-weight: 900;
            color: rgba(255, 255, 255, .72);
        }
        .todays-deal-carousel {
            position: relative;
            z-index: 1;
            padding: 0 34px;
        }
        .todays-deal-carousel .slick-track {
            display: flex;
            align-items: stretch;
        }
        .todays-deal-carousel .slick-slide {
            height: auto;
        }
        .todays-deal-carousel .slick-arrow {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .92);
            color: #f59e0b;
            box-shadow: 0 8px 20px rgba(124, 45, 18, .18);
        }
        .todays-deal-product-card {
            display: block;
            min-width: 0;
            padding: 0 8px;
            text-align: center;
        }
        .todays-deal-item-circle {
            background: transparent;
            border: 1px solid #f97316;
            border-radius: 50%;
            width: 118px;
            height: 118px;
            margin: 0 auto;
            position: relative;
            padding: 0;
            overflow: hidden;
        }
        .todays-deal-product-title {
            display: -webkit-box;
            height: 38px;
            max-width: 150px;
            margin-right: auto;
            margin-left: auto;
            overflow: hidden;
            text-overflow: ellipsis;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            color: #111827;
            font-size: 13px;
            line-height: 1.35;
            font-weight: 800;
            margin-top: 12px;
            padding: 0 4px;
        }
        .todays-deal-product-price {
            color: #7c2d12;
            font-size: 15px;
            font-weight: 900;
            margin-top: 4px;
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
        @media (min-width: 1200px) {
            .todays-deal-yellow-section {
                padding: 54px 38px;
            }
        }
        @media (max-width: 767px) {
            .todays-deal-yellow-section {
                padding: 28px 12px;
                border-radius: 10px;
            }
            .todays-deal-yellow-section h2 {
                font-size: 28px;
            }
            .todays-deal-header-row {
                gap: 16px;
                margin-bottom: 22px;
            }
            .todays-deal-urgency-copy {
                font-size: 13px;
            }
            .todays-deal-countdown {
                width: 100%;
                max-width: 340px;
            }
            .todays-deal-countdown__unit {
                min-width: 38px;
            }
            .todays-deal-countdown__value {
                font-size: 18px;
            }
            .todays-deal-item-circle {
                width: 104px;
                height: 104px;
            }
            .todays-deal-carousel {
                padding: 0 26px;
            }
            .todays-deal-product-card {
                padding: 0 5px;
            }
            .todays-deal-product-title {
                max-width: 126px;
                height: 34px;
                font-size: 12px;
                line-height: 1.32;
            }
        }
    </style>

    <section class="mb-4">
        <div class="container">
            <div class="todays-deal-yellow-section has-transition">
                <div class="todays-deal-illustration left d-none d-xl-block"></div>
                <div class="todays-deal-illustration right d-none d-xl-block"></div>

                <div class="todays-deal-header-row">
                    <div>
                        <h2 class="mb-0">{{ translate('Specially Made For U') }}</h2>
                        <div class="deal-btn mt-3">{{ translate("Today's Deal") }}</div>
                        <p class="todays-deal-urgency-copy">
                            {{ translate('Featured products selected for today only. The countdown resets every night at midnight.') }}
                        </p>
                    </div>

                    <div class="todays-deal-countdown" data-metro-todays-countdown aria-label="{{ translate('Time remaining before today\'s deals reset') }}">
                        <span class="todays-deal-countdown__unit">
                            <span class="todays-deal-countdown__value" data-countdown-part="days">00</span>
                            <span class="todays-deal-countdown__label">{{ translate('JJ') }}</span>
                        </span>
                        <span class="todays-deal-countdown__separator">:</span>
                        <span class="todays-deal-countdown__unit">
                            <span class="todays-deal-countdown__value" data-countdown-part="hours">00</span>
                            <span class="todays-deal-countdown__label">{{ translate('HH') }}</span>
                        </span>
                        <span class="todays-deal-countdown__separator">:</span>
                        <span class="todays-deal-countdown__unit">
                            <span class="todays-deal-countdown__value" data-countdown-part="minutes">00</span>
                            <span class="todays-deal-countdown__label">{{ translate('MM') }}</span>
                        </span>
                        <span class="todays-deal-countdown__separator">:</span>
                        <span class="todays-deal-countdown__unit">
                            <span class="todays-deal-countdown__value" data-countdown-part="seconds">00</span>
                            <span class="todays-deal-countdown__label">{{ translate('SS') }}</span>
                        </span>
                    </div>
                </div>

                <div class="todays-deal-carousel aiz-carousel dots-white"
                    data-items="6" data-xxl-items="8" data-xl-items="6" data-lg-items="5" data-md-items="4"
                    data-sm-items="3" data-xs-items="2" data-autoplay="true" data-infinite="true"
                    data-dots="false" data-arrows="true">
                    @foreach ($todays_deal_products->take(16) as $product)
                        <div class="carousel-box">
                            <a href="{{ route('product', $product->slug) }}" class="todays-deal-product-card text-reset">
                                <div class="todays-deal-item-circle hov-scale-img has-transition">
                                    <img src="{{ get_image($product->thumbnail, 'medium') }}" class="img-fit h-100 w-100 rounded-circle" alt="{{ $product->getTranslation('name') }}" onerror="this.src='{{ static_asset('assets/img/placeholder.jpg') }}'">
                                </div>
                                <span class="todays-deal-product-title">{{ $product->getTranslation('name') }}</span>
                                <div class="todays-deal-product-price">{{ home_discounted_base_price($product) }}</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
