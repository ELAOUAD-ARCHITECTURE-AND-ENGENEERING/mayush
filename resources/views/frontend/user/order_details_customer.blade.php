@extends('frontend.layouts.user_panel')

@section('page_title', translate('Order details'))

@section('panel_content')
    @php
        $firstOrderDetail = $order->orderDetails->first();
        $gstin = get_seller_gstin($order);
        $shippingAddress = json_decode($order->shipping_address) ?: (object) [];
        $billingAddress = json_decode($order->billing_address) ?: null;
        $onlineMethods = get_activate_payment_methods()->where('name', '!=', 'cmi')->values();
        $manualMethods = addon_is_activated('offline_payment') ? get_all_manual_payment_methods() : collect();
        $hasCmi = (int) get_setting('cmi_payment') === 1;
        $hasWallet = (int) get_setting('wallet_system') === 1 && auth()->user()->balance >= $order->grand_total;
        $hasOnlineMethods = $onlineMethods->isNotEmpty() || $hasCmi || $hasWallet;
        $canPay = $order->payment_status === 'unpaid'
            && $order->delivery_status === 'pending'
            && (int) $order->manual_payment === 0;
        $deliveryStatusKey = str_replace('_', '-', strtolower((string) $order->delivery_status));
        $paymentStatusKey = str_replace('_', '-', strtolower((string) $order->payment_status));
        $addressText = function ($address) {
            if (!$address) {
                return translate('No address provided');
            }

            return collect([
                data_get($address, 'address'),
                data_get($address, 'city'),
                data_get($address, 'state'),
                data_get($address, 'postal_code'),
                data_get($address, 'country'),
            ])->filter()->implode(', ');
        };
    @endphp

    <style>
        .buyer-order-page {
            --order-navy: #111827;
            --order-ink: #1f2937;
            --order-muted: #64748b;
            --order-line: #e2e8f0;
            --order-canvas: #f8fafc;
            --order-teal: #0f766e;
            --order-teal-bright: #14b8a6;
            --order-teal-soft: #ecfdf5;
            --order-orange: {{ get_setting('base_color', '#e0782f') }};
            --order-orange-soft: #fff7ed;
            color: var(--order-ink);
            font-family: var(--mayush-font-body, Inter, sans-serif);
        }

        .buyer-order-page .buyer-order-hero {
            align-items: flex-end;
            background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 72%);
            border: 1px solid #99f6e4;
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 118, 110, .08);
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin-bottom: 18px;
            padding: 24px 26px;
        }

        .buyer-order-page .order-eyebrow {
            color: var(--order-teal);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .buyer-order-page .order-title {
            color: var(--order-navy);
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: clamp(24px, 3vw, 34px);
            font-weight: 700;
            letter-spacing: -.025em;
            line-height: 1.15;
            margin: 0 0 8px;
        }

        .buyer-order-page .order-meta {
            color: var(--order-muted);
            font-size: 13px;
            margin: 0;
        }

        .buyer-order-page .order-hero-actions {
            align-items: flex-end;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .buyer-order-page .order-statuses,
        .buyer-order-page .order-status-strip {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .buyer-order-page .order-status {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            gap: 6px;
            line-height: 1;
            padding: 9px 12px;
        }

        .buyer-order-page .order-status::before {
            background: currentColor;
            border-radius: 50%;
            content: '';
            height: 6px;
            width: 6px;
        }

        .buyer-order-page .order-status--pending,
        .buyer-order-page .order-status--unpaid { background: #fff7ed; color: #c2410c; }
        .buyer-order-page .order-status--confirmed,
        .buyer-order-page .order-status--paid,
        .buyer-order-page .order-status--delivered { background: #ecfdf5; color: #047857; }
        .buyer-order-page .order-status--cancelled { background: #fef2f2; color: #b91c1c; }
        .buyer-order-page .order-status--on-the-way,
        .buyer-order-page .order-status--picked-up { background: #eff6ff; color: #1d4ed8; }

        .buyer-order-page .order-back-link {
            color: var(--order-teal);
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .buyer-order-page .order-back-link:hover { color: var(--order-navy); }

        .buyer-order-page .buyer-order-card {
            background: #fff;
            border: 1px solid var(--order-line);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
            margin-bottom: 18px;
            overflow: hidden;
        }

        .buyer-order-page .buyer-order-card__header {
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            padding: 18px 20px;
        }

        .buyer-order-page .buyer-order-card__title {
            color: var(--order-navy);
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .buyer-order-page .buyer-order-card__body { padding: 20px; }

        .buyer-order-page .order-fact-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .buyer-order-page .order-fact {
            background: var(--order-canvas);
            border: 1px solid #eef2f7;
            border-radius: 12px;
            min-height: 78px;
            padding: 13px 14px;
        }

        .buyer-order-page .order-fact__label {
            color: #94a3b8;
            display: block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .buyer-order-page .order-fact__value {
            color: var(--order-ink);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .buyer-order-page .order-address-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 14px;
        }

        .buyer-order-page .order-address {
            border: 1px solid var(--order-line);
            border-radius: 12px;
            padding: 15px;
        }

        .buyer-order-page .order-address__title {
            align-items: center;
            color: var(--order-navy);
            display: flex;
            font-size: 12px;
            font-weight: 800;
            gap: 8px;
            margin-bottom: 8px;
        }

        .buyer-order-page .order-address__title svg { color: var(--order-teal-bright); }
        .buyer-order-page .order-address__text { color: var(--order-muted); font-size: 12px; line-height: 1.65; margin: 0; }

        .buyer-order-page .order-items-table { margin: 0; min-width: 620px; }
        .buyer-order-page .order-items-table th {
            border-bottom: 1px solid var(--order-line);
            color: #94a3b8;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .06em;
            padding: 0 10px 12px;
            text-transform: uppercase;
        }
        .buyer-order-page .order-items-table td { border-bottom: 1px solid #f1f5f9; padding: 16px 10px; vertical-align: middle; }
        .buyer-order-page .order-items-table tr:last-child td { border-bottom: 0; }
        .buyer-order-page .order-item__name { color: var(--order-navy); font-size: 13px; font-weight: 700; line-height: 1.45; }
        .buyer-order-page .order-item__name a { color: inherit; text-decoration: none; }
        .buyer-order-page .order-item__name a:hover { color: var(--order-teal); }
        .buyer-order-page .order-item__meta { color: var(--order-muted); font-size: 11px; margin-top: 4px; }
        .buyer-order-page .order-item__price { color: var(--order-navy); font-size: 13px; font-weight: 800; white-space: nowrap; }
        .buyer-order-page .order-item__muted { color: var(--order-muted); font-size: 12px; }

        .buyer-order-page .order-total-card { position: sticky; top: 88px; }
        .buyer-order-page .order-total-list { margin: 0; }
        .buyer-order-page .order-total-row { align-items: center; color: var(--order-muted); display: flex; font-size: 13px; justify-content: space-between; padding: 9px 0; }
        .buyer-order-page .order-total-row strong { color: var(--order-ink); font-weight: 700; }
        .buyer-order-page .order-total-row--grand { border-top: 1px solid var(--order-line); color: var(--order-navy); font-size: 16px; font-weight: 800; margin-top: 8px; padding-top: 18px; }
        .buyer-order-page .order-total-row--grand strong { color: var(--order-teal); font-size: 20px; }

        .buyer-order-page .order-pay-button {
            align-items: center;
            background: var(--order-orange);
            border: 0;
            border-radius: 10px;
            box-shadow: 0 8px 18px rgba(224, 120, 47, .22);
            color: #fff;
            display: flex;
            font-size: 13px;
            font-weight: 800;
            gap: 8px;
            justify-content: center;
            margin-top: 18px;
            min-height: 48px;
            padding: 12px 16px;
            transition: background 180ms ease, box-shadow 180ms ease, transform 180ms ease;
            width: 100%;
        }

        .buyer-order-page .order-pay-button:hover { background: #c86423; box-shadow: 0 10px 22px rgba(224, 120, 47, .3); color: #fff; transform: translateY(-1px); }
        .buyer-order-page .order-pay-button:focus-visible,
        .buyer-order-page .order-method:focus-within { box-shadow: 0 0 0 3px rgba(20, 184, 166, .25); outline: 0; }

        .buyer-order-page .order-secure-note { color: var(--order-muted); font-size: 11px; line-height: 1.5; margin: 12px 0 0; text-align: center; }
        .buyer-order-page .order-payment-note { background: var(--order-teal-soft); border-radius: 10px; color: var(--order-teal); font-size: 12px; line-height: 1.5; margin-top: 14px; padding: 12px; }

        .buyer-order-page .modal-content { border: 0; border-radius: 18px; box-shadow: 0 24px 70px rgba(15, 23, 42, .22); overflow: hidden; }
        .buyer-order-page .modal-header { background: var(--order-navy); border: 0; color: #fff; padding: 18px 22px; }
        .buyer-order-page .modal-title { font-size: 17px; font-weight: 800; }
        .buyer-order-page .modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
        .buyer-order-page .modal-body { padding: 22px; }
        .buyer-order-page .payment-method-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .buyer-order-page .order-method { cursor: pointer; display: block; margin: 0; }
        .buyer-order-page .order-method input { height: 1px; opacity: 0; position: absolute; width: 1px; }
        .buyer-order-page .order-method__card { align-items: center; border: 1px solid var(--order-line); border-radius: 12px; display: flex; gap: 12px; min-height: 72px; padding: 12px; transition: border-color 180ms ease, background 180ms ease, box-shadow 180ms ease; }
        .buyer-order-page .order-method__logo { align-items: center; background: #fff; border: 1px solid #eef2f7; border-radius: 8px; display: flex; flex: 0 0 48px; height: 42px; justify-content: center; padding: 6px; }
        .buyer-order-page .order-method__logo img { max-height: 28px; max-width: 42px; object-fit: contain; }
        .buyer-order-page .order-method__name { color: var(--order-navy); font-size: 12px; font-weight: 800; }
        .buyer-order-page .order-method__hint { color: var(--order-muted); display: block; font-size: 10px; font-weight: 500; margin-top: 3px; }
        .buyer-order-page .order-method input:checked + .order-method__card { background: var(--order-teal-soft); border-color: var(--order-teal-bright); box-shadow: 0 0 0 2px rgba(20, 184, 166, .12); }
        .buyer-order-page .order-empty-state { background: var(--order-canvas); border: 1px dashed #cbd5e1; border-radius: 12px; color: var(--order-muted); font-size: 13px; line-height: 1.6; padding: 18px; text-align: center; }
        .buyer-order-page .order-modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .buyer-order-page .order-modal-footer .btn { border-radius: 9px; font-size: 12px; font-weight: 800; min-height: 40px; padding: 9px 16px; }
        .buyer-order-page .order-modal-footer .btn-primary { background: var(--order-teal); border-color: var(--order-teal); }
        .buyer-order-page .order-modal-footer .btn-primary:hover { background: var(--order-navy); border-color: var(--order-navy); }
        .buyer-order-page .offline-payment-modal-body { background: var(--order-canvas); }

        @media (max-width: 767.98px) {
            .buyer-order-page .buyer-order-hero { align-items: flex-start; flex-direction: column; padding: 20px; }
            .buyer-order-page .order-hero-actions { align-items: flex-start; }
            .buyer-order-page .order-fact-grid,
            .buyer-order-page .order-address-grid,
            .buyer-order-page .payment-method-grid { grid-template-columns: 1fr; }
            .buyer-order-page .buyer-order-card__body { padding: 16px; }
            .buyer-order-page .order-total-card { position: static; }
            .buyer-order-page .modal-body { padding: 18px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .buyer-order-page .order-pay-button,
            .buyer-order-page .order-method__card { transition: none; }
        }
    </style>

    <div class="buyer-order-page">
        <div class="buyer-order-hero">
            <div>
                <div class="order-eyebrow">{{ translate('Order details') }}</div>
                <h1 class="order-title">{{ translate('Order') }} #{{ $order->code }}</h1>
                <p class="order-meta">
                    {{ translate('Placed on') }} {{ date('d M Y, H:i', $order->date) }}
                    <span class="mx-1" aria-hidden="true">·</span>
                    {{ $order->orderDetails->count() }} {{ translate('item(s)') }}
                </p>
            </div>
            <div class="order-hero-actions">
                <div class="order-statuses">
                    <span class="order-status order-status--{{ $deliveryStatusKey }}">
                        {{ translate(ucfirst(str_replace('_', ' ', $order->delivery_status))) }}
                    </span>
                    <span class="order-status order-status--{{ $paymentStatusKey }}">
                        {{ translate(ucfirst($order->payment_status)) }}
                    </span>
                </div>
                <a class="order-back-link" href="{{ route('purchase_history.index') }}">
                    <i class="las la-arrow-left mr-1" aria-hidden="true"></i>{{ translate('Back to orders') }}
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <section class="buyer-order-card" aria-labelledby="order-overview-title">
                    <div class="buyer-order-card__header">
                        <h2 class="buyer-order-card__title" id="order-overview-title">{{ translate('Order overview') }}</h2>
                        <span class="text-muted fs-12">{{ translate('Customer copy') }}</span>
                    </div>
                    <div class="buyer-order-card__body">
                        <div class="order-fact-grid">
                            <div class="order-fact">
                                <span class="order-fact__label">{{ translate('Order code') }}</span>
                                <span class="order-fact__value">{{ $order->code }}</span>
                            </div>
                            <div class="order-fact">
                                <span class="order-fact__label">{{ translate('Customer') }}</span>
                                <span class="order-fact__value">{{ data_get($shippingAddress, 'name', auth()->user()->name) }}</span>
                            </div>
                            <div class="order-fact">
                                <span class="order-fact__label">{{ translate('Email') }}</span>
                                <span class="order-fact__value">{{ $order->user?->email ?: data_get($shippingAddress, 'email', '—') }}</span>
                            </div>
                            <div class="order-fact">
                                <span class="order-fact__label">{{ translate('Payment method') }}</span>
                                <span class="order-fact__value">{{ ucfirst(translate(str_replace('_', ' ', $order->payment_type))) }}</span>
                            </div>
                        </div>

                        <div class="order-address-grid">
                            <div class="order-address">
                                <div class="order-address__title">
                                    <i class="las la-truck" aria-hidden="true"></i>{{ translate('Shipping address') }}
                                </div>
                                <p class="order-address__text">{{ $addressText($shippingAddress) }}</p>
                            </div>
                            <div class="order-address">
                                <div class="order-address__title">
                                    <i class="las la-file-invoice" aria-hidden="true"></i>{{ translate('Billing address') }}
                                </div>
                                <p class="order-address__text">{{ $addressText($billingAddress ?: $shippingAddress) }}</p>
                            </div>
                        </div>

                        @if ($gstin != null && $firstOrderDetail && is_numeric($firstOrderDetail->gst_amount))
                            <div class="mt-3 text-muted fs-12"><strong>{{ translate('GSTIN') }}:</strong> {{ $gstin }}</div>
                        @endif
                        @if ($order->additional_info)
                            <div class="mt-3 text-muted fs-12"><strong>{{ translate('Additional information') }}:</strong> {{ $order->additional_info }}</div>
                        @endif
                    </div>
                </section>

                <section class="buyer-order-card" aria-labelledby="order-items-title">
                    <div class="buyer-order-card__header">
                        <h2 class="buyer-order-card__title" id="order-items-title">{{ translate('Order items') }}</h2>
                        <span class="text-muted fs-12">{{ $order->orderDetails->count() }} {{ translate('item(s)') }}</span>
                    </div>
                    <div class="buyer-order-card__body p-0">
                        <div class="table-responsive px-3 px-md-4">
                            <table class="table order-items-table">
                                <thead>
                                    <tr>
                                        <th>{{ translate('Product') }}</th>
                                        <th>{{ translate('Qty') }}</th>
                                        <th>{{ translate('Delivery') }}</th>
                                        <th class="text-right">{{ translate('Price') }}</th>
                                        @if (addon_is_activated('refund_request'))<th class="text-right">{{ translate('Review') }}</th>@endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->orderDetails as $orderDetail)
                                        @php $product = $orderDetail->product; @endphp
                                        <tr>
                                            <td>
                                                <div class="order-item__name">
                                                    @if ($product && $product->auction_product == 0)
                                                        <a href="{{ route('product', $product->slug) }}" target="_blank">{{ $product->getTranslation('name') }}</a>
                                                    @elseif ($product && $product->auction_product == 1)
                                                        <a href="{{ route('auction-product', $product->slug) }}" target="_blank">{{ $product->getTranslation('name') }}</a>
                                                    @else
                                                        {{ translate('Product unavailable') }}
                                                    @endif
                                                </div>
                                                <div class="order-item__meta">{{ $orderDetail->variation ?: translate('Standard item') }}</div>
                                            </td>
                                            <td class="order-item__muted">{{ $orderDetail->quantity }}</td>
                                            <td class="order-item__muted">
                                                @if ($order->shipping_type === 'home_delivery')
                                                    {{ translate('Home delivery') }}
                                                @elseif ($order->shipping_type === 'pickup_point')
                                                    {{ $order->pickup_point?->name ?: translate('Pickup point') }}
                                                @elseif ($order->shipping_type === 'carrier')
                                                    {{ $order->carrier?->name ?: translate('Carrier') }}
                                                @else
                                                    {{ translate('Standard delivery') }}
                                                @endif
                                            </td>
                                            <td class="order-item__price text-right">{{ single_price($orderDetail->price) }}</td>
                                            @if (addon_is_activated('refund_request'))
                                                <td class="text-right">
                                                    @php
                                                        $refundDays = $orderDetail->refund_days;
                                                        $lastRefundDate = $order->delivered_date && $refundDays > 0 ? CarbonCarbon::parse($order->delivered_date)->addDays($refundDays) : null;
                                                    @endphp
                                                    @if ($orderDetail->product && !$orderDetail->refund_request && $lastRefundDate && CarbonCarbon::now() <= $lastRefundDate && $order->payment_status === 'paid' && $order->delivery_status === 'delivered')
                                                        <a href="{{ route('refund_request_send_page', $orderDetail->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill">{{ translate('Request') }}</a>
                                                    @elseif ($orderDetail->refund_request)
                                                        <span class="order-item__muted">{{ translate('In review') }}</span>
                                                    @else
                                                        <span class="order-item__muted">{{ translate('—') }}</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="col-xl-4">
                <section class="buyer-order-card order-total-card" aria-labelledby="order-total-title">
                    <div class="buyer-order-card__header">
                        <h2 class="buyer-order-card__title" id="order-total-title">{{ translate('Payment summary') }}</h2>
                        <i class="las la-receipt text-teal" aria-hidden="true"></i>
                    </div>
                    <div class="buyer-order-card__body">
                        <div class="order-total-list">
                            <div class="order-total-row"><span>{{ translate('Subtotal') }}</span><strong>{{ single_price($order->orderDetails->sum('price')) }}</strong></div>
                            <div class="order-total-row"><span>{{ translate('Shipping') }}</span><strong>{{ single_price($order->orderDetails->sum('shipping_cost')) }}</strong></div>
                            @if ($firstOrderDetail && is_numeric($firstOrderDetail->gst_amount))
                                <div class="order-total-row"><span>{{ translate('Tax') }}</span><strong>{{ single_price($order->orderDetails->sum('gst_amount')) }}</strong></div>
                            @else
                                <div class="order-total-row"><span>{{ translate('Tax') }}</span><strong>{{ single_price($order->orderDetails->sum('tax')) }}</strong></div>
                            @endif
                            <div class="order-total-row"><span>{{ translate('Coupon') }}</span><strong>{{ single_price($order->coupon_discount) }}</strong></div>
                            <div class="order-total-row order-total-row--grand"><span>{{ translate('Total') }}</span><strong>{{ single_price($order->grand_total) }}</strong></div>
                        </div>

                        @if ($canPay)
                            <button type="button" class="order-pay-button" onclick="{{ $manualMethods->isNotEmpty() ? 'select_payment_type()' : 'online_payment()' }}">
                                <i class="las la-lock" aria-hidden="true"></i>{{ translate('Complete payment') }}
                            </button>
                            <p class="order-secure-note"><i class="las la-shield-alt mr-1" aria-hidden="true"></i>{{ translate('Choose a payment method to finish this order securely.') }}</p>
                        @elseif ($order->manual_payment)
                            <div class="order-payment-note"><i class="las la-hourglass-half mr-1" aria-hidden="true"></i>{{ translate('Your payment proof is awaiting verification.') }}</div>
                        @endif
                    </div>
                </section>

                @if ($order->tracking_code)
                    <section class="buyer-order-card">
                        <div class="buyer-order-card__header"><h2 class="buyer-order-card__title">{{ translate('Tracking') }}</h2></div>
                        <div class="buyer-order-card__body"><span class="text-muted fs-12">{{ translate('Tracking code') }}</span><div class="fw-700 mt-1">{{ $order->tracking_code }}</div></div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection

@section('modal')
    <div class="buyer-order-page">
        <div class="modal fade" id="product-review-modal" tabindex="-1" role="dialog" aria-labelledby="product-review-title" aria-hidden="true">
            <div class="modal-dialog"><div class="modal-content" id="product-review-modal-content"></div></div>
        </div>

        @if ($canPay && $manualMethods->isNotEmpty())
            <div class="modal fade" id="payment_type_select_modal" tabindex="-1" role="dialog" aria-labelledby="payment-type-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="payment-type-title">{{ translate('Choose how to pay') }}</h5><button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">&times;</button></div>
                    <div class="modal-body">
                        <p class="text-muted fs-13 mb-3">{{ translate('Select the payment route you want to use for this order.') }}</p>
                        <div class="payment-method-grid">
                            @if ($hasOnlineMethods)
                                <label class="order-method" onclick="payment_modal('online')"><input type="radio" name="payment_type_choice" value="online"><span class="order-method__card"><span class="order-method__logo"><i class="las la-credit-card fs-22 text-teal"></i></span><span><span class="order-method__name">{{ translate('Online payment') }}</span><span class="order-method__hint">{{ translate('Card and available gateways') }}</span></span></span></label>
                            @endif
                            <label class="order-method" onclick="payment_modal('offline')"><input type="radio" name="payment_type_choice" value="offline"><span class="order-method__card"><span class="order-method__logo"><i class="las la-university fs-22 text-teal"></i></span><span><span class="order-method__name">{{ translate('Bank transfer') }}</span><span class="order-method__hint">{{ translate('Upload your payment proof') }}</span></span></span></label>
                        </div>
                    </div>
                </div></div>
            </div>
        @endif

        @if ($canPay)
            <div class="modal fade" id="online_payment_modal" tabindex="-1" role="dialog" aria-labelledby="online-payment-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="online-payment-title">{{ translate('Choose online payment') }}</h5><button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">&times;</button></div>
                    <form action="{{ route('order.re_payment') }}" method="post">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        <div class="modal-body">
                            @if ($hasOnlineMethods)
                                @php $onlineSelected = false; @endphp
                                <div class="payment-method-grid">
                                    @foreach ($onlineMethods as $paymentMethod)
                                        @php $checked = !$onlineSelected; $onlineSelected = true; @endphp
                                        <label class="order-method">
                                            <input value="{{ $paymentMethod->name }}" type="radio" name="payment_option" required {{ $checked ? 'checked' : '' }}>
                                            <span class="order-method__card"><span class="order-method__logo"><img src="{{ static_asset('assets/img/cards/' . $paymentMethod->name . '.png') }}" alt="{{ $paymentMethod->name }}" onerror="this.style.display='none';"></span><span><span class="order-method__name">{{ ucfirst(translate($paymentMethod->name)) }}</span><span class="order-method__hint">{{ translate('Secure gateway') }}</span></span></span>
                                        </label>
                                    @endforeach
                                    @if ($hasCmi)
                                        @php $checked = !$onlineSelected; $onlineSelected = true; @endphp
                                        <label class="order-method">
                                            <input value="cmi" type="radio" name="payment_option" required {{ $checked ? 'checked' : '' }}>
                                            <span class="order-method__card"><span class="order-method__logo"><img src="{{ static_asset('assets/img/cards/cmi.png') }}" alt="CMI"></span><span><span class="order-method__name">{{ translate('CMI payment') }}</span><span class="order-method__hint">{{ translate('Card and 3-D Secure') }}</span></span></span>
                                        </label>
                                    @endif
                                    @if ($hasWallet)
                                        @php $checked = !$onlineSelected; $onlineSelected = true; @endphp
                                        <label class="order-method">
                                            <input value="wallet" type="radio" name="payment_option" required {{ $checked ? 'checked' : '' }}>
                                            <span class="order-method__card"><span class="order-method__logo"><i class="las la-wallet fs-22 text-teal"></i></span><span><span class="order-method__name">{{ translate('Wallet') }}</span><span class="order-method__hint">{{ translate('Balance available') }}</span></span></span>
                                        </label>
                                    @endif
                                </div>
                            @else
                                <div class="order-empty-state" role="alert"><i class="las la-info-circle fs-24 d-block mb-2 text-teal"></i>{{ translate('No online payment method is available for this order right now.') }}<br><small>{{ translate('Please contact support or choose bank transfer if available.') }}</small></div>
                            @endif
                            <div class="order-modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button><button type="submit" class="btn btn-primary" {{ !$hasOnlineMethods ? 'disabled' : '' }}>{{ translate('Continue to payment') }} <i class="las la-arrow-right ml-1" aria-hidden="true"></i></button></div>
                        </div>
                    </form>
                </div></div>
            </div>

            <div class="modal fade" id="offline_order_re_payment_modal" tabindex="-1" role="dialog" aria-labelledby="offline-payment-title" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title" id="offline-payment-title">{{ translate('Bank transfer payment') }}</h5><button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">&times;</button></div>
                    <div id="offline_order_re_payment_modal_body" class="offline-payment-modal-body"></div>
                </div></div>
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        function product_review(productId, orderId) {
            $.post('{{ route('product_review_modal') }}', {
                _token: '{{ csrf_token() }}',
                product_id: productId,
                order_id: orderId
            }).done(function (data) {
                $('#product-review-modal-content').html(data);
                $('#product-review-modal').modal('show', { backdrop: 'static' });
                if (window.AIZ && AIZ.extra) AIZ.extra.inputRating();
            });
        }

        function online_payment() {
            $('#online_payment_modal').modal('show', { backdrop: 'static' });
        }

        function select_payment_type() {
            $('#payment_type_select_modal').modal('show', { backdrop: 'static' });
        }

        function payment_modal(type) {
            $('#payment_type_select_modal').modal('hide');
            if (type === 'online') {
                $('#online_payment_modal').modal('show', { backdrop: 'static' });
                return;
            }

            $('#offline_order_re_payment_modal_body').html('<div class="p-4 text-center text-muted"><i class="las la-spinner la-spin fs-22"></i><div class="mt-2">{{ translate('Loading payment methods') }}</div></div>');
            $('#offline_order_re_payment_modal').modal('show', { backdrop: 'static' });
            $.post('{{ route('offline_order_re_payment_modal') }}', {
                _token: '{{ csrf_token() }}',
                order_id: '{{ $order->id }}'
            }).done(function (data) {
                $('#offline_order_re_payment_modal_body').html(data);
            }).fail(function (xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ translate('Unable to load payment methods. Please try again.') }}';
                $('#offline_order_re_payment_modal_body').html('<div class="p-4 text-center text-danger" role="alert">' + message + '</div>');
            });
        }
    </script>
@endsection
