@extends('frontend.layouts.app')

@section('styles')
    <style>
        .order-confirmation-recommendations {
            --recommendation-gap: 8px;
            background: var(--mayush-white, #fff);
            border: 1px solid var(--mayush-border, #e5e0d8);
            border-radius: var(--mayush-radius-xl, 12px);
            box-shadow: var(--mayush-shadow-card, 0 2px 8px rgba(0, 0, 0, .08));
            padding: clamp(18px, 2.4vw, 28px);
        }

        .order-confirmation-recommendations__header {
            align-items: flex-end;
            border-bottom: 1px solid var(--mayush-border, #e5e0d8);
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 18px;
            padding-bottom: 14px;
        }

        .order-confirmation-recommendations__title {
            color: var(--mayush-black, #1a1a1a);
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
            margin: 0;
        }

        .order-confirmation-recommendations__title::before {
            background: var(--mayush-orange, #d97434);
            border-radius: 999px;
            content: '';
            display: inline-block;
            height: 7px;
            margin-right: 9px;
            vertical-align: 2px;
            width: 7px;
        }

        .order-confirmation-recommendations__hint {
            color: var(--mayush-text-muted, #666);
            font-size: 12px;
            margin: 0;
            white-space: nowrap;
        }

        .order-confirmation-slider {
            margin: 0 calc(var(--recommendation-gap) * -1);
        }

        .order-confirmation-slider .slick-list {
            margin: 0;
            padding: 3px 0 8px;
        }

        .order-confirmation-slider .slick-track {
            display: flex;
        }

        .order-confirmation-slider .slick-slide {
            display: flex;
            float: none;
            height: auto;
        }

        .order-confirmation-slider .slick-slide > div,
        .order-confirmation-slider .carousel-box {
            display: flex;
            height: 100%;
            width: 100%;
        }

        .order-confirmation-slider .carousel-box {
            padding: 0 var(--recommendation-gap);
        }

        .order-confirmation-product-card {
            border: 1px solid var(--mayush-border, #e5e0d8);
            border-radius: var(--mayush-radius-lg, 8px);
            box-shadow: none;
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
            transition: border-color var(--mayush-transition-base, .3s ease), box-shadow var(--mayush-transition-base, .3s ease), transform var(--mayush-transition-base, .3s ease);
            width: 100%;
        }

        .order-confirmation-product-card:hover {
            border-color: rgba(217, 116, 52, .55);
            box-shadow: var(--mayush-shadow-card-hover, 0 8px 24px rgba(0, 0, 0, .12));
            transform: translateY(-2px);
        }

        .order-confirmation-product-card__image {
            aspect-ratio: 1 / .82;
            background: var(--mayush-soft-beige, #f5f1e8);
            display: block;
            overflow: hidden;
            position: relative;
        }

        .order-confirmation-product-card__image img {
            height: 100%;
            object-fit: cover;
            transition: transform var(--mayush-transition-slow, .5s ease);
            width: 100%;
        }

        .order-confirmation-product-card__badge {
            background: var(--mayush-orange, #d97434);
            border-radius: 0 0 6px 0;
            color: var(--mayush-white, #fff);
            font-size: 11px;
            font-weight: 700;
            left: 0;
            line-height: 1;
            padding: 7px 9px;
            position: absolute;
            top: 0;
            z-index: 1;
        }

        .order-confirmation-product-card:hover .order-confirmation-product-card__image img {
            transform: scale(1.035);
        }

        .order-confirmation-product-card__body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 12px 13px 14px;
        }

        .order-confirmation-product-card__name {
            font-size: 13px;
            font-weight: 500;
            line-height: 1.45;
            margin: 0 0 9px;
            min-height: 38px;
        }

        .order-confirmation-product-card__name a {
            color: var(--mayush-text, #333);
            display: -webkit-box;
            overflow: hidden;
            text-decoration: none;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            transition: color var(--mayush-transition-fast, .15s ease);
        }

        .order-confirmation-product-card__name a:hover {
            color: var(--mayush-orange, #d97434);
        }

        .order-confirmation-product-card__price {
            align-items: baseline;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: auto;
        }

        .order-confirmation-product-card__price-current {
            color: var(--mayush-price, #9f4f18);
            font-size: 14px;
            font-weight: 700;
        }

        .order-confirmation-product-card__price-old {
            color: var(--mayush-text-light, #999);
            font-size: 12px;
        }

        .order-confirmation-slider .slick-arrow {
            background: var(--mayush-white, #fff) !important;
            border: 1px solid var(--mayush-border, #e5e0d8) !important;
            color: var(--mayush-black, #1a1a1a) !important;
            height: 34px !important;
            line-height: 32px !important;
            width: 34px !important;
        }

        .order-confirmation-slider .slick-prev {
            left: -7px !important;
        }

        .order-confirmation-slider .slick-next {
            right: -7px !important;
        }

        .order-confirmation-slider .slick-arrow:hover {
            background: var(--mayush-orange, #d97434) !important;
            border-color: var(--mayush-orange, #d97434) !important;
            color: var(--mayush-white, #fff) !important;
        }

        .order-confirmation-slider .slick-arrow:focus-visible {
            outline: 2px solid var(--mayush-orange, #d97434);
            outline-offset: 2px;
        }

        @media (max-width: 767.98px) {
            .order-confirmation-recommendations {
                padding: 16px 12px;
            }

            .order-confirmation-recommendations__header {
                align-items: flex-start;
                flex-direction: column;
                gap: 5px;
                margin-bottom: 14px;
                padding-bottom: 12px;
            }

            .order-confirmation-recommendations__hint {
                white-space: normal;
            }

            .order-confirmation-slider .slick-prev {
                left: -5px !important;
            }

            .order-confirmation-slider .slick-next {
                right: -5px !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .order-confirmation-product-card,
            .order-confirmation-product-card__image img,
            .order-confirmation-product-card__name a {
                transition: none;
            }
        }
    </style>
@endsection

@section('content')

    <!-- Steps -->
    @include('frontend.partials.checkout.stepper', ['step' => 5])

    <!-- Order Confirmation -->
    <section class="py-4">
        <div class="container text-left">
            <div class="row">
                <div class="col-xl-8 mx-auto">
                    @php
                        $first_order = $combined_order->orders->first()
                    @endphp
                    <!-- Order Confirmation Text-->
                    <div class="text-center py-4 mb-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 36 36" class=" mb-3">
                            <g id="Group_23983" data-name="Group 23983" transform="translate(-978 -481)">
                              <circle id="Ellipse_44" data-name="Ellipse 44" cx="18" cy="18" r="18" transform="translate(978 481)" fill="#85b567"/>
                              <g id="Group_23982" data-name="Group 23982" transform="translate(32.439 8.975)">
                                <rect id="Rectangle_18135" data-name="Rectangle 18135" width="11" height="3" rx="1.5" transform="translate(955.43 487.707) rotate(45)" fill="#fff"/>
                                <rect id="Rectangle_18136" data-name="Rectangle 18136" width="3" height="18" rx="1.5" transform="translate(971.692 482.757) rotate(45)" fill="#fff"/>
                              </g>
                            </g>
                        </svg>
                        <h1 class="mb-2 fs-28 fw-500 text-success">{{ translate('Thank You for Your Order!')}}</h1>
                        <p class="fs-13 text-soft-dark">{{  translate('A copy or your order summary has been sent to') }} <strong>{{ json_decode($first_order->shipping_address)->email }}</strong></p>
                    </div>
                    <!-- Order Summary -->
                    <div class="mb-4 bg-white p-4 border">
                        <h5 class="fw-600 mb-3 fs-16 text-soft-dark pb-2 border-bottom">{{ translate('Order Summary')}}</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table fs-14 text-soft-dark">
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 pl-0 py-2">{{ translate('Order date')}}:</td>
                                        <td class="border-top-0 py-2">{{ date('d-m-Y H:i A', $first_order->date) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 pl-0 py-2">{{ translate('Name')}}:</td>
                                        <td class="border-top-0 py-2">{{ json_decode($first_order->shipping_address)->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 pl-0 py-2">{{ translate('Email')}}:</td>
                                        <td class="border-top-0 py-2">{{ json_decode($first_order->shipping_address)->email }}</td>
                                    </tr>
                                    <tr>
                                        @php
                                            $shipping = json_decode($first_order->shipping_address);
                                            $billing = json_decode($first_order->billing_address);
                                        @endphp
                                        <td class="w-50 fw-600 border-top-0 pl-0 py-2">{{ translate('Shipping address')}}:</td>
                                        <td class="border-top-0 py-2">
                                            {{ $shipping->address }}, 
                                            {{ $shipping?->city ? $shipping->city . ', ' : '' }}
                                            {{ isset($shipping->state) ? $shipping->state . ', ' : '' }}
                                            {{ $shipping->country }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 pl-0 py-2">{{ translate('Billing address')}}:</td>
                                        <td class="border-top-0 py-2">
                                            {{ $billing->address }}, 
                                            {{ $billing?->city ? $billing->city . ', ' : '' }}
                                            {{ isset($billing->state) ? $billing->state . ', ' : '' }}
                                            {{ $billing->country }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table">
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Order status')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">{{ translate(ucfirst(str_replace('_', ' ', $first_order->delivery_status))) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Total order amount')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">{{ single_price($combined_order->grand_total) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Shipping')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">{{ translate('Flat shipping rate')}}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Payment method')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">{{ translate(ucfirst(str_replace('_', ' ', $first_order->payment_type))) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Estimated Delivery')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">3-5 {{ translate('Business Days') }}</td>
                                    </tr>
                                    @php
                                        $payment_details = json_decode($first_order->payment_details);
                                    @endphp
                                    @if($payment_details && isset($payment_details->TransId))
                                    <tr>
                                        <td class="w-50 fw-600 border-top-0 py-2">{{ translate('Transaction ID')}}:</td>
                                        <td class="border-top-0 pr-0 py-2">{{ $payment_details->TransId }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Suggestions & Last Viewed -->
                    @php
                        $related_products = get_related_products_by_category($first_order->orderDetails->first()->product?->category_id);
                        $last_viewed_products = Auth::check() ? getLastViewedProducts() : null;
                    @endphp

                    @if (count($related_products) > 0)
                        <section class="order-confirmation-recommendations mb-4" aria-labelledby="suggested-products-title" role="region">
                            <div class="order-confirmation-recommendations__header">
                                <h2 id="suggested-products-title" class="order-confirmation-recommendations__title">{{ translate('Suggested for You')}}</h2>
                                <p class="order-confirmation-recommendations__hint">{{ translate('Complete your space with more pieces you may love') }}</p>
                            </div>
                            <div class="aiz-carousel order-confirmation-slider arrow-inactive-none" data-items="4" data-xxl-items="4" data-xl-items="4" data-lg-items="3" data-md-items="3" data-sm-items="2" data-xs-items="1.2" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true" aria-label="{{ translate('Suggested products') }}">
                                @foreach ($related_products as $related_product)
                                    <div class="carousel-box">
                                        @include('frontend.partials.order_confirmation_product_card', ['product' => $related_product])
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($last_viewed_products && count($last_viewed_products) > 0)
                        <section class="order-confirmation-recommendations mb-4" aria-labelledby="last-viewed-products-title" role="region">
                            <div class="order-confirmation-recommendations__header">
                                <h2 id="last-viewed-products-title" class="order-confirmation-recommendations__title">{{ translate('Your Last Viewed Products')}}</h2>
                                <p class="order-confirmation-recommendations__hint">{{ translate('Pick up where you left off') }}</p>
                            </div>
                            <div class="aiz-carousel order-confirmation-slider arrow-inactive-none" data-items="4" data-xxl-items="4" data-xl-items="4" data-lg-items="3" data-md-items="3" data-sm-items="2" data-xs-items="1.2" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true" aria-label="{{ translate('Your last viewed products') }}">
                                @foreach ($last_viewed_products as $item)
                                    @php $last_product = $item->product; @endphp
                                    @if ($last_product)
                                        <div class="carousel-box">
                                            @include('frontend.partials.order_confirmation_product_card', ['product' => $last_product])
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <!-- Orders Info -->
                    @foreach ($combined_order->orders as $order)
                        <div class="card shadow-none border rounded-0">
                            <div class="card-body">
                                <!-- Order Code -->
                                <div class="text-center py-1 mb-4">
                                    <h2 class="h5 fs-20">{{ translate('Order Code:')}} <span class="fw-700 text-primary">{{ $order->code }}</span></h2>
                                    <h5 class="h5 fs-14">{{ translate('Delivery Type:')}} 
                                        <span class="fw-700">
                                            @if ($order->shipping_type != null && $order->shipping_type == 'home_delivery')
                                                {{  translate('Home Delivery') }}
                                            @elseif ($order->shipping_type != null && $order->shipping_type == 'carrier')
                                                {{  translate('Carrier') }}
                                            @elseif ($order->shipping_type == 'pickup_point')
                                                @if ($order->pickup_point != null)
                                                    {{ $order->pickup_point->getTranslation('name') }} ({{ translate('Pickip Point') }})
                                                @endif
                                            @endif
                                        </span>
                                    </h5>
                                    @if(get_seller_gstin($order) != null)
                                    <h5 class="h5 fs-14">{{ translate('GSTIN')}}: 
                                        <span class="fw-700">
                                            {{get_seller_gstin($order)}}
                                        </span>
                                    </h5>
                                    @endif
                                </div>
                                <!-- Order Details -->
                                <div>
                                    <h5 class="fw-600 text-soft-dark mb-3 fs-16 pb-2">{{ translate('Order Details')}}</h5>
                                    <!-- Product Details -->
                                    <div>
                                        <table class="table table-responsive-md text-soft-dark fs-14">
                                            <thead>
                                                <tr>
                                                    <th class="opacity-60 border-top-0 pl-0">#</th>
                                                    <th class="opacity-60 border-top-0" width="30%">{{ translate('Product')}}</th>
                                                    <th class="opacity-60 border-top-0">{{ translate('Qty')}}</th>
                                                    @if(addon_is_activated('gst_system'))
                                                    <th class="opacity-60 border-top-0">{{ translate('Gross Amount')}}</th>
                                                    <th class="opacity-60 border-top-0">{{ translate('Discount/ Coupon')}}</th>
                                                    <th class="opacity-60 border-top-0">{{ translate('Taxable Value')}}</th>
                                                    @if(same_state_shipping($order))
                                                    <th class="opacity-60 border-top-0">{{ translate('CGST')}}</th>
                                                    <th class="opacity-60 border-top-0">{{ translate('SGST')}}</th>
                                                    @else
                                                    <th class="opacity-60 border-top-0">{{ translate('IGST')}}</th>
                                                    @endif
                                                    @endif

                                                    <th class="text-right opacity-60 border-top-0 pr-0">{{ translate('Price')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($order->orderDetails as $key => $orderDetail)
                                                    <tr>
                                                        <td class="border-top-0 border-bottom pl-0">{{ $key+1 }}</td>
                                                        <td class="border-top-0 border-bottom">
                                                            @if ($orderDetail->product != null)
                                                                <a href="{{ route('product', $orderDetail->product->slug) }}" target="_blank" class="text-reset">
                                                                    {{ $orderDetail->product->getTranslation('name') }}
                                                                    @php
                                                                        if($orderDetail->combo_id != null) {
                                                                            $combo = \App\ComboProduct::findOrFail($orderDetail->combo_id);

                                                                            echo '('.$combo->combo_title.')';
                                                                        }
                                                                    @endphp
                                                                </a>
                                                                <p class="fs-12">{{ $orderDetail->variation }}</p>
                                                            @else
                                                                <strong>{{  translate('Product Unavailable') }}</strong>
                                                            @endif
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ $orderDetail->quantity }}
                                                        </td>
                                                        @if(addon_is_activated('gst_system'))
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($orderDetail->price) }}
                                                        </td>

                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($orderDetail->coupon_discount) }}
                                                        </td>

                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($orderDetail->price - $orderDetail->coupon_discount) }}
                                                        </td>

                                                        @php 
                                                            $gst_amount = get_gst_by_price_and_rate($orderDetail->price - $orderDetail->coupon_discount , $orderDetail->gst_rate);
                                                            $shipping_gst = get_gst_by_price_and_rate($orderDetail->shipping_cost, $orderDetail->gst_rate);
                                                            @endphp
                                                        @if(same_state_shipping($order))
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($gst_amount/2) }}
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($gst_amount/2) }}
                                                        </td>
                                                        @else
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($gst_amount) }}
                                                        </td>
                                                        @endif
                                                        @endif
                                                        @if(addon_is_activated('gst_system'))
                                                        <td class="border-top-0 border-bottom pr-0 text-right">{{ single_price($orderDetail->price - $orderDetail->coupon_discount + $gst_amount) }}</td>
                                                        @else
                                                        <td class="border-top-0 border-bottom pr-0 text-right">{{ single_price($orderDetail->price) }}</td>
                                                        @endif
                                                    </tr>
                                                    @if(addon_is_activated('gst_system'))
                                                    <tr>
                                                        <td class="border-top-0 border-bottom pl-0"></td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{translate('Shipping')}}
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            1
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($orderDetail->shipping_cost) }}
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price(0) }}
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($orderDetail->shipping_cost) }}
                                                        </td>
                                                        @if(same_state_shipping($order))
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($shipping_gst/2) }}
                                                        </td>
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($shipping_gst/2) }}
                                                        </td>
                                                        @else
                                                        <td class="border-top-0 border-bottom">
                                                            {{ single_price($shipping_gst) }}
                                                        </td>
                                                        @endif
                                                        <td class="border-top-0 border-bottom pr-0 text-right">{{ single_price($orderDetail->shipping_cost + (($orderDetail->shipping_cost* $orderDetail->gst_rate)/100)) }}
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Order Amounts -->
                                    <div class="row">
                                        <div class="col-xl-5 col-md-6 ml-auto mr-0">
                                            <table class="table ">
                                                <tbody>
                                                    <!-- Subtotal -->
                                                     @if(addon_is_activated('gst_system'))
                                                     <tr>
                                                        <th class="border-top-0 py-2">{{ translate('Subtotal')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span class="fw-600">{{ single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('shipping_cost') - $order->orderDetails->sum('coupon_discount')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="border-top-0 py-2">{{ translate('GST Amount')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span>{{ single_price($order->orderDetails->sum('gst_amount')) }}</span>
                                                        </td>
                                                    </tr>
                                                    @else
                                                    <tr>
                                                        <th class="border-top-0 py-2">{{ translate('Subtotal')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span class="fw-600">{{ single_price($order->orderDetails->sum('price')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <!-- Shipping -->
                                                    <tr>
                                                        <th class="border-top-0 py-2">{{ translate('Shipping')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span>{{ single_price($order->orderDetails->sum('shipping_cost')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <!-- Tax -->
                                                    <tr>
                                                        <th class="border-top-0 py-2">{{ translate('Tax')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span>{{ single_price($order->orderDetails->sum('tax')) }}</span>
                                                        </td>
                                                    </tr>
                                                    <!-- Coupon Discount -->
                                                    <tr>
                                                        <th class="border-top-0 py-2">{{ translate('Coupon Discount')}}</th>
                                                        <td class="text-right border-top-0 pr-0 py-2">
                                                            <span>{{ single_price($order->coupon_discount) }}</span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    <!-- Total -->
                                                    <tr>
                                                        <th class="py-2"><span class="fw-600">{{ translate('Total')}}</span></th>
                                                        <td class="text-right pr-0">
                                                            <strong><span>{{ single_price($order->grand_total) }}</span></strong>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    @if (get_setting('facebook_pixel') == 1)
    <!-- Facebook Pixel purchase Event -->
    <script>
        $(document).ready(function(){
            var currend_code = '{{ get_system_currency()->code }}';
            var amount = 'single_price($combined_order->grand_total) }}';
            fbq('track', 'Purchase',
                {
                    value: amount,
                    currency: currend_code,
                    content_type: 'product'
                }
            );
        });
    </script>
    <!-- Facebook Pixel purchase Event -->
    @endif
@endsection
        
