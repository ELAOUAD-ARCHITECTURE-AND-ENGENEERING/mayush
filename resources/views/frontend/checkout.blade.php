@extends('frontend.layouts.app')

@section('content')
    <style>
        :root {
            --premium-gold: {{ get_setting('base_color', '#ff6700') }};
            --brand-vivid: {{ get_setting('base_color', '#ff6700') }};
            --premium-dark: #1a1a1a;
            --premium-soft: #fcfcfc;
            --premium-gray: #f8f9fa;
            --premium-accent: {{ hex2rgba(get_setting('base_color', '#ff6700'), 0.1) }};
        }

        .premium-checkout-container {
            font-family: 'Outfit', sans-serif;
            background-color: var(--premium-soft);
            min-height: 100vh;
            padding: 40px 0;
        }

        .checkout-wrapper {
            border-radius: 20px;
            background: white;
            margin-left: 1px;
            margin-right: 1px;
        }

        .checkout-main-content {
            background: white;
            padding: 40px;
            border-right: 1px solid #f0f0f0;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        .checkout-sidebar {
            background: var(--premium-gray);
            padding: 40px;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .checkout-sidebar .sticky-top {
            z-index: 5 !important;
        }
        
        @media (max-width: 991px) {
            .checkout-main-content {
                border-right: none;
                border-bottom-left-radius: 0;
                border-top-right-radius: 20px;
            }
            .checkout-sidebar {
                border-top-right-radius: 0;
                border-bottom-left-radius: 20px;
            }
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--brand-vivid);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .checkout-main-content h3, 
        .checkout-main-content h5,
        .checkout-main-content .nav-tabs .nav-link.active {
            color: var(--brand-vivid) !important;
            font-weight: 700 !important;
        }

        .section-title .step-number {
            width: 28px;
            height: 28px;
            background: var(--brand-vivid);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            margin-right: 12px;
            box-shadow: 0 4px 10px {{ hex2rgba(get_setting('base_color', '#ff6700'), 0.3) }};
        }

        .premium-checkout-card {
            border: 1px solid #eee !important;
            border-radius: 16px !important;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: white;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .premium-checkout-card:hover {
            border-color: var(--premium-gold) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        .card-header-premium {
            padding: 24px;
            background: transparent;
            border-bottom: 1px solid #f9f9f9;
        }

        .card-body-premium {
            padding: 24px;
        }

        .action-button-main {
            background: var(--premium-dark);
            color: white;
            border: none;
            padding: 18px 36px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }

        .action-button-main:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .action-button-main:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
        }

        /* Float Labels & Modern Inputs */
        .form-control-premium {
            border: 1px solid #e0e0e0;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control-premium:focus {
            border-color: var(--premium-gold);
            box-shadow: 0 0 0 4px rgba(226, 176, 74, 0.05);
            outline: none;
        }

        /* Order Summary Item */
        .summary-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .summary-item-img {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eee;
            margin-right: 15px;
        }

        /* Floating AI Badge */
        .ai-insight-badge {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .insight-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #edf2f7;
            margin-top: 20px;
        }

        /* Desktop specific layout */
        @media (min-width: 992px) {
            .premium-checkout-container {
                display: flex;
            }
            .checkout-main-content {
                flex: 1;
                max-width: 65%;
            }
            .checkout-sidebar {
                width: 35%;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        /* Validation GSAP */
        .premium-input-error {
            border-color: #e74c3c !important;
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 6px;
            display: none;
        }

        /* Loading Spinner Overlay */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(5px);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--premium-accent);
            border-top: 4px solid var(--premium-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Premium Modal Styles */
        .premium-modal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .premium-modal .modal-header {
            background: var(--premium-gray);
            border-bottom: 1px solid #f0f0f0;
            border-radius: 16px 16px 0 0;
            padding: 24px;
        }
        .premium-modal .modal-title {
            font-weight: 700;
            color: var(--premium-dark);
        }
        .premium-modal .modal-body {
            padding: 24px;
        }
        .premium-modal .form-control, 
        .premium-modal .bootstrap-select > .dropdown-toggle {
            border: 1px solid #e0e0e0;
            border-radius: 12px !important;
            padding: 12px 18px;
            height: auto;
            background: var(--premium-soft);
            box-shadow: none !important;
            transition: all 0.3s ease;
        }
        .premium-modal .form-control:focus,
        .premium-modal .bootstrap-select > .dropdown-toggle:focus {
            border-color: var(--premium-gold);
            background: white;
        }
        .premium-modal label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .premium-modal .btn-primary {
            background: var(--premium-dark);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
        }
        .premium-modal .btn-primary:hover {
            background: var(--premium-gold);
        }
    </style>
    <section class="premium-checkout-container">
        <!-- Loading Overlay -->
        <div id="loading-overlay">
            <div class="spinner"></div>
            <h4 class="fs-18 fw-700 text-dark">{{ translate('Processing your elegant order...') }}</h4>
            <p class="fs-14 text-muted">{{ translate('Please wait while we secure your transaction') }}</p>
        </div>
        
        <div class="container">
            <div class="row shadow-lg checkout-wrapper">
                <!-- Main Checkout Flow -->
                <div class="col-lg-8 checkout-main-content animate-fade-in p-lg-5">
                    <div class="mb-5">
                <h1 class="fs-24 fw-800 text-dark mb-1">{{ translate('Checkout') }}</h1>
                <p class="text-muted">{{ translate('Review your order and choose your preferences.') }}</p>
            </div>

            @php
                $isEligible = \App\Services\PaymentVaultService::isEligible();
                $preferredMethod = \App\Services\PaymentVaultService::getPreferredPaymentMethod();
            @endphp

            @if ($isEligible)
                <!-- 1-Click Purchase Banner -->
                <div class="card border-0 mb-5 overflow-hidden" style="background: linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div class="text-white mb-3 mb-md-0">
                            <h3 class="fs-18 fw-700 mb-1 d-flex align-items-center">
                                <i class="las la-unlock-alt mr-2 text-warning"></i>
                                {{ translate('Elegant Vault Active') }}
                            </h3>
                            <p class="fs-14 opacity-70 mb-0">{{ translate('Purchase with your saved preferences in one click.') }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <form action="{{ route('checkout.fast_purchase') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary px-4 py-3 fw-700 rounded-pill d-flex align-items-center" style="background: var(--premium-gold); border: none; color: #000; box-shadow: 0 4px 15px rgba(226, 176, 74, 0.3);">
                                    <i class="las la-bolt fs-20 mr-2"></i>
                                    {{ translate('1-CLICK PURCHASE') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <form class="form-default" data-toggle="validator" action="{{ route('payment.checkout') }}" role="form" method="POST" id="checkout-form">
                @csrf
                
                <!-- 1. Shipping Information -->
                <div class="mb-5">
                    <div class="section-title">
                        <span class="step-number">1</span>
                        {{ translate('Shipping Information') }}
                    </div>
                    <div id="shipping_info">
                        @include('frontend.partials.cart.shipping_info', ['address_id' => $address_id])
                    </div>
                </div>

                <!-- 2. Delivery Method -->
                <div class="mb-5">
                    <div class="section-title">
                        <span class="step-number">2</span>
                        {{ translate('Delivery Method') }}
                    </div>
                    <div id="delivery_info">
                        @include('frontend.partials.cart.delivery_info', ['carts' => $carts, 'carrier_list' => $carrier_list, 'shipping_info' => $shipping_info])
                    </div>
                </div>

                <!-- 3. Payment Details -->
                <div class="mb-5">
                    <div class="section-title">
                        <span class="step-number">3</span>
                        {{ translate('Payment Details') }}
                    </div>
                    <div id="payment_info">
                        @include('frontend.partials.cart.payment_info', ['carts' => $carts, 'total' => $total])
                    </div>
                </div>

                <!-- Agreement & Completion -->
                <div class="premium-checkout-card p-4 bg-light border-0">
                    <div class="fs-14 mb-4">
                        <label class="aiz-checkbox">
                            <input type="checkbox" required id="agree_checkbox" onchange="stepCompletionPaymentInfo()">
                            <span class="aiz-square-check"></span>
                            <span>{{ translate('I agree to the') }}</span>
                        </label>
                        <a href="{{ route('terms') }}" class="fw-700 text-dark">{{ translate('terms and conditions') }}</a>,
                        <a href="{{ route('returnpolicy') }}" class="fw-700 text-dark">{{ translate('return policy') }}</a> &
                        <a href="{{ route('privacypolicy') }}" class="fw-700 text-dark">{{ translate('privacy policy') }}</a>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('home') }}" class="btn btn-link text-dark fw-700 p-0">
                            <i class="las la-arrow-left"></i> {{ translate('Back to Store') }}
                        </a>
                        <button type="button" onclick="submitOrder(this)" id="submitOrderBtn" class="action-button-main" style="width: auto; min-width: 250px;">
                            {{ translate('Complete Order') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sticky Sidebar -->
        <div class="col-lg-4 checkout-sidebar p-lg-5">
            <div class="sticky-top" style="top: 40px;">
                <!-- Order Summary Partial -->
                <div id="cart_summary">
                    @include('frontend.partials.cart.cart_summary', ['proceed' => 0, 'carts' => $carts])
                </div>

                @php
                    $cart_total = 0;
                    $cart_count = count($carts);
                    foreach ($carts as $key => $cartItem) {
                        $cart_total += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
                    }
                    $white_glove_threshold = 5000;
                @endphp
                <!-- AI Design Insights Section -->
                <div class="insight-card mt-4 box-shadow-sm">
                    <div class="ai-insight-badge">
                        <i class="las la-magic mr-1"></i> {{ translate('AI DESIGN COMPANION') }}
                    </div>
                    <h4 class="fs-16 fw-700 text-dark mb-2">{{ translate('Premium Insights') }}</h4>
                    <ul class="list-unstyled fs-13 text-muted mb-0">
                        <li class="mb-3 d-flex">
                            <i class="las la-check-circle text-success mr-2 mt-1"></i>
                            @if($cart_count > 1)
                            <span>{{ translate('Your ') }} {{ $cart_count }} {{ translate(' selections share a cohesive aesthetic. Excellent pairing.') }}</span>
                            @else
                            <span>{{ translate('A perfect choice. Consider pairing this with a matching accessory to complete the look.') }}</span>
                            @endif
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="las la-truck text-primary mr-2 mt-1"></i>
                            @if($cart_total >= $white_glove_threshold)
                            <span>{{ translate('Your order qualifies for our exclusive "White Glove" installation service!') }}</span>
                            @else
                            <span>{{ translate('You are only ') }} <strong>{{ single_price($white_glove_threshold - $cart_total) }}</strong> {{ translate(' away from unlocking our "White Glove" installation service.') }}</span>
                            @endif
                        </li>
                        <li class="d-flex">
                            <i class="las la-shield-alt text-info mr-2 mt-1"></i>
                            <span>{{ translate('Each piece is inspected by our quality artisans before shipping.') }}</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-4 p-3 rounded-lg border border-dashed text-center">
                    <p class="fs-12 text-muted mb-0">
                        <i class="las la-lock mr-1"></i> {{ translate('Secure 256-bit SSL Encrypted Connection') }}
                    </p>
                </div>
            </div>
        </div>
        
            </div>
        </div>
    </section>
@endsection

@section('modal')
    <!-- Address Modal -->
    @if(Auth::check())
        @include('frontend.partials.address.address_modal')
        @include('frontend.partials.address.billing_address_modal')
    @endif
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script type="text/javascript">
        gsap.registerPlugin(ScrollTrigger);
        var carrierCount = 0;

        $(document).ready(function() {
            // Initial reveal of main content and sections
            gsap.from(".checkout-main-content > div", {
                opacity: 0,
                y: 30,
                duration: 0.8,
                stagger: 0.1,
                ease: "expo.out"
            });

            // Handle payment options toggle
            $(".online_payment, .offline_payment_option").click(function() {
                $('#manual_payment_description').parent().addClass('d-none');
                stepCompletionPaymentInfo();
            });
            
            toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));

            // Initial state checks
            carrierCount = parseInt(document.getElementById('carrierCount')?.value || 0);
            checkCarrerShippingInfo();
            stepCompletionShippingInfo();
            stepCompletionDeliveryInfo();
            stepCompletionPaymentInfo();
        });

        // Minimum order settings
        var minimum_order_amount_check = {{ get_setting('minimum_order_amount_check') == 1 ? 1 : 0 }};
        var minimum_order_amount = {{ get_setting('minimum_order_amount_check') == 1 ? get_setting('minimum_order_amount') : 0 }};

        function submitOrder(el) {
            let $btn = $(el);
            $btn.prop('disabled', true).addClass('btn-loading');
            
            if ($('#agree_checkbox').is(":checked")) {
                var subtotal = parseFloat($('#sub_total').val()) || 0;
                
                if (minimum_order_amount_check && subtotal < minimum_order_amount) {
                    AIZ.plugins.notify('danger', '{{ translate('Your order amount is less than the minimum order amount') }}');
                    $btn.prop('disabled', false).removeClass('btn-loading');
                } else {
                    var offline_payment_active = '{{ addon_is_activated('offline_payment') }}';
                    var isOfflineChecked = $('.offline_payment_option').is(":checked");
                    
                    if (offline_payment_active == '1' && isOfflineChecked && $('#trx_id').val() == '') {
                        AIZ.plugins.notify('danger', '{{ translate('You need to provide a Transaction ID') }}');
                        $btn.prop('disabled', false).removeClass('btn-loading');
                    } else {
                        // Validate sections
                        var isOkShipping = stepCompletionShippingInfo();
                        var isOkDelivery = stepCompletionDeliveryInfo();
                        var isOkPayment = stepCompletionPaymentInfo();

                        if(isOkShipping && isOkDelivery && isOkPayment) {
                            // Premium GSAP feedback
                            gsap.to(el, { scale: 0.95, duration: 0.1, yoyo: true, repeat: 1 });
                            $('#loading-overlay').css('display', 'flex');
                            gsap.fromTo('#loading-overlay', {opacity: 0}, {opacity: 1, duration: 0.3});

                            // AJAX Submission
                            let formData = new FormData($('#checkout-form')[0]);
                            $.ajax({
                                url: $('#checkout-form').attr('action'),
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(data) {
                                    if (data.status === 'success') {
                                        // Final "Order Secured" Animation
                                        gsap.to('#loading-overlay h4', { 
                                            text: '{{ translate("Elegance Secured.") }}', 
                                            duration: 0.5, 
                                            color: 'var(--premium-gold)' 
                                        });
                                        
                                        setTimeout(() => {
                                            if (data.type === 'redirect') {
                                                window.location.href = data.url;
                                            } else if (data.type === 'html') {
                                                // Inject hidden form (like CMI) and submit
                                                $('body').append('<div id="temp-payment-form" style="display:none">' + data.html + '</div>');
                                                // CMI form has name="cmi_form" and submits on body onload, 
                                                // but since we inject it, we trigger it manually
                                                if ($('form[name="cmi_form"]').length) {
                                                    document.cmi_form.submit();
                                                } else {
                                                    $('#temp-payment-form form').submit();
                                                }
                                            }
                                        }, 1000);
                                    } else {
                                        $('#loading-overlay').fadeOut();
                                        AIZ.plugins.notify('danger', data.message || '{{ translate("An error occurred.") }}');
                                        $btn.prop('disabled', false).removeClass('btn-loading');
                                    }
                                },
                                error: function(xhr) {
                                    $('#loading-overlay').fadeOut();
                                    let msg = '{{ translate("Something went wrong. Please try again.") }}';
                                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                    AIZ.plugins.notify('danger', msg);
                                    $btn.prop('disabled', false).removeClass('btn-loading');
                                }
                            });
                        } else {
                            AIZ.plugins.notify('danger', '{{ translate("Please complete all required fields.") }}');
                            $btn.prop('disabled', false).removeClass('btn-loading');
                            
                            // Highlight first missing field
                            $('#checkout-form [required]').each(function (i, el) {
                                let $this = $(this);
                                if (!$this.val() || ($this.is(':checkbox') && !$this.is(':checked')) || ($this.is(':radio') && $('input[name="'+$this.attr('name')+'"]:checked').length === 0)) {
                                    let targetParent = $this.closest('.form-control, .aiz-checkbox, .aiz-radio');
                                    if(targetParent.length === 0) targetParent = $this;
                                    
                                    targetParent.addClass('premium-input-error');
                                    setTimeout(() => { targetParent.removeClass('premium-input-error'); }, 400);
                                    
                                    $this.focus();
                                    $('html, body').animate({ scrollTop: $this.offset().top - 100 }, 500);
                                    return false;
                                }
                            });
                        }
                    }
                }
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Please agree to our terms and conditions.') }}');
                $btn.prop('disabled', false).removeClass('btn-loading');
            }
        }

        function toggleManualPaymentData(id) {
            if (id && id !== 'undefined') {
                $('#manual_payment_description').parent().removeClass('d-none');
                $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
            }
        }

        function updateDeliveryAddress(id, city_id = 0, area_id = 0) {
            $('.checkout-main-content').css('opacity', '0.6');
            $.post('{{ route('checkout.updateDeliveryAddress') }}', {
                _token: AIZ.data.csrf,
                address_id: id,
                city_id: city_id,
                area_id: area_id
            }, function(data) {
                $('#delivery_info').html(data.delivery_info);
                $('#cart_summary').html(data.cart_summary);
                $('.checkout-main-content').css('opacity', '1');
                carrierCount = data.carrier_count;
                checkCarrerShippingInfo();
                gsap.from("#delivery_info", { opacity: 0, y: 10, duration: 0.4 });
            });
            AIZ.plugins.bootstrapSelect("refresh");
        }

        function updateBillingAddress(id) {
            $.post('{{ route('checkout.updateBillingAddress') }}', {
                _token: AIZ.data.csrf,
                address_id: id
            });
        }

        function stepCompletionShippingInfo() {
            var allOk = false;
            @if (Auth::check())
                if ($('input[name="address_id"]:checked').length > 0) allOk = true;
            @else
                var count = 0;
                var length = $('#shipping_info [required]').length;
                $('#shipping_info [required]').each(function () {
                    if ($(this).val()) count++;
                });
                if (count == length) allOk = true;
            @endif
            return allOk;
        }

        $('#shipping_info [required]').each(function (i, el) {
            $(el).change(function(){
                if ($(el).attr('name') == 'address_id') {
                    updateDeliveryAddress($(el).val());
                    setDefaultshippingAddress();
                    setBillingAddress();
                }
                @if (get_setting('shipping_type') == 'area_wise_shipping')
                    if ($(el).attr('name') == 'city_id') {
                        let country_id = $('select[name="country_id"]').length? $('select[name="country_id"]').val() : $('input[name="country_id"]').val();
                        let city_id = $(this).val();
                        updateDeliveryAddress(country_id, city_id);
                    }
                @endif
                
                stepCompletionShippingInfo();
            });
        });

        function stepCompletionDeliveryInfo() {
            return $('.delivery_shipping_cost:checked').length > 0 || $('.pickup_point_id:checked').length > 0;
        }

        function updateDeliveryInfo(shipping_type, type_id, user_id, country_id = 0, city_id = 0) {
            @if (get_setting('shipping_type') == 'area_wise_shipping' || get_setting('shipping_type') == 'carrier_wise_shipping')
                country_id = $('select[name="country_id"]').val() != null ? $('select[name="country_id"]').val() : 0;
                city_id = $('select[name="city_id"]').val() != null ? $('select[name="city_id"]').val() : 0;
            @endif
            $('.aiz-refresh').addClass('active');
            $.post('{{ route('checkout.updateDeliveryInfo') }}', {
                _token: AIZ.data.csrf,
                shipping_type: shipping_type,
                type_id: type_id,
                user_id: user_id,
                country_id: country_id,
                city_id: city_id
            }, function(data) {
                $('#cart_summary').html(data);
                checkCarrerShippingInfo();
                stepCompletionDeliveryInfo();
                $('.aiz-refresh').removeClass('active');
            });
            AIZ.plugins.bootstrapSelect("refresh");
        }

        function show_pickup_point(el, user_id) {
        	var type = $(el).val();
        	var target = $(el).data('target');
            var type_id = null;

        	if(type == 'home_delivery' || type == 'carrier'){
                if(!$(target).hasClass('d-none')){
                    $(target).addClass('d-none');
                }
                $('.carrier_id_'+user_id).removeClass('d-none');
        	}else{
        		$(target).removeClass('d-none');
        		$('.carrier_id_'+user_id).addClass('d-none');
        	}

            if(type == 'carrier'){
                type_id = $('input[name=carrier_id_'+user_id+']:checked').val();
            }else if(type == 'pickup_point'){
                type_id = $('select[name=pickup_point_id_'+user_id+']').val();
            }
            updateDeliveryInfo(type, type_id, user_id);
        }

        function stepCompletionPaymentInfo() {
            var isChecked = $('input[name="payment_option"]:checked').length > 0;
            var agree = $('#agree_checkbox').is(":checked");
            $("#submitOrderBtn").prop('disabled', !(isChecked && agree));
            return isChecked;
        }

        $('input[name="payment_option"]').change(function(){
            stepCompletionPaymentInfo();
        });

        function checkCarrerShippingInfo(){
            const shippingType = @json(get_setting('shipping_type'));
            const isCarrier = $('.shipping-type-radio:checked[value="carrier"]').length > 0;
            if(shippingType == 'carrier_wise_shipping' && isCarrier && carrierCount === 0){
                $('#submitOrderBtn').prop('disabled', true);
                $('#agree_checkbox').prop('disabled',true);
            } else {
                $('#agree_checkbox').prop('disabled', false);
            }
        }

        function changeShippingAddress(){
            $('#choose-address-modal').modal('hide');
        }

        function setDefaultshippingAddress() {
            let checkedAddress = $('input[name="address_id"]:checked');

            if (checkedAddress.length) {

                let selectedText = checkedAddress.closest('label').find('.address-text').html();
                $('#choose-default').html(selectedText);
                $('#default-address-change-btn').attr('onclick', "edit_address('" + checkedAddress.val() + "')");
                $('input[name="billing_address_id"]').first().val(checkedAddress.val());
                let $box = $('#default-address-box');
                if ($box.length) {
                    $box.removeClass('border-danger');
                    checkedAddress.prop('checked', true);
                    checkedAddress.prop('disabled', false);
                    $box.find('#hide-no-longer-div').remove();
                    
                }
            }
        }

        function setBillingAddress() {
            let checkedAddress = $('input[name="billing_address_id"]:checked');
            if (checkedAddress.length) {
                let selectedText = checkedAddress.closest('label').find('.address-text').html();
                $('#choose-default-billing').html(selectedText);
                $('#default-billing-address-change-btn').attr('onclick', "edit_billing_address('" + checkedAddress.val() + "')");
                let $box = $('#default-billing-address-box');
                if ($box.length) {
                    $box.removeClass('border-danger');
                    checkedAddress.prop('checked', true);
                    checkedAddress.prop('disabled', false);
                    $box.find('#hide-no-valid-div').remove();
                }
            } else {
                // If no billing address is explicitly checked, use the shipping address as default
                let shippingAddress = $('input[name="address_id"]:checked');
                if (shippingAddress.length) {
                    let selectedText = shippingAddress.closest('label').find('.address-text').html();
                    $('#choose-default-billing').html(selectedText);
                    $('input[name="billing_address_id"]').first().val(shippingAddress.val());
                }
            }
            updateBillingAddress(checkedAddress.val() || $('input[name="address_id"]:checked').val());
        }

        $(document).on("click", "#coupon-apply", function() {
            var data = new FormData($('#apply-coupon-form')[0]);
            $.ajax({
                headers: { 'X-CSRF-TOKEN': AIZ.data.csrf },
                method: "POST",
                url: "{{ route('checkout.apply_coupon_code') }}",
                data: data,
                cache: false, contentType: false, processData: false,
                success: function(data) {
                    AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                    $("#cart_summary").html(data.html);
                }
            });
        });

        $(document).on("click", "#coupon-remove", function() {
            var data = new FormData($('#remove-coupon-form')[0]);
            $.ajax({
                headers: { 'X-CSRF-TOKEN': AIZ.data.csrf },
                method: "POST",
                url: "{{ route('checkout.remove_coupon_code') }}",
                data: data,
                cache: false, contentType: false, processData: false,
                success: function(data) {
                    $("#cart_summary").html(data);
                }
            });
        });
    </script>

    @include('frontend.partials.address.address_js')

    @if(get_active_countries()->count() == 1)
    <script>
        $(document).ready(function() {
            @if(get_setting('has_state') == 1)
                get_states(@json(get_active_countries()[0]->id));
            @else
                get_city_by_country(@json(get_active_countries()[0]->id));
            @endif
        });
    </script>
    @endif

    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection
