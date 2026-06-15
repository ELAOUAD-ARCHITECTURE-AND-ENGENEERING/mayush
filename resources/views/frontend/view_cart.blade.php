@extends('frontend.layouts.app')

@section('content')
    <!-- Cart Details -->
    <section class="my-4" id="cart-details">
        @include('frontend.partials.cart.cart_details', ['carts' => $carts])
    </section>

    @php
        $suggestions = \App\Services\CartEnrichmentService::getSuggestions(4);
    @endphp
    @if ($suggestions->count() > 0)
        <section class="mb-5">
            <div class="container">
                <div class="px-3 py-4 bg-white shadow-sm">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-circle bg-soft-primary mr-3" style="width: 40px; height: 40px; border-radius: 50%; background: rgba(226, 176, 74, 0.1); display: flex; align-items: center; justify-content: center;">
                            <i class="las la-magic fs-20" style="color: #e2b04a;"></i>
                        </div>
                        <h4 class="fs-18 fw-700 mb-0 text-uppercase" style="letter-spacing: 1px;">{{ translate('Complete Your Style') }}</h4>
                    </div>
                    <div class="row gutters-10">
                        @foreach ($suggestions as $suggestion)
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <div class="card h-100 border-0 shadow-none has-transition hov-shadow-md suggestion-item p-3" style="border: 1px solid #f1f1f1 !important; border-radius: 12px;">
                                    <div class="position-relative mb-3">
                                        <a href="{{ route('product', $suggestion->slug) }}" class="d-block">
                                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}" data-src="{{ uploaded_asset($suggestion->thumbnail_img, 'thumb') }}" 
                                                 class="img-fit h-200px lazyload rounded" alt="{{ $suggestion->name }}">
                                        </a>
                                        <button type="button" class="btn btn-primary btn-icon btn-circle position-absolute bottom-0 right-0 m-2" 
                                                style="background: #e2b04a; border: none; color: #000;"
                                                onclick="quickAddToCart({{ $suggestion->id }}, this)">
                                            <i class="las la-plus"></i>
                                        </button>
                                    </div>
                                    <div class="text-left">
                                        <h5 class="fs-14 fw-600 mb-2">
                                            <a href="{{ route('product', $suggestion->slug) }}" class="text-reset text-truncate-2">{{ $suggestion->getTranslation('name') }}</a>
                                        </h5>
                                        <div class="fs-15 fw-700 text-primary">{{ single_price($suggestion->unit_price) }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

@endsection

@section('script')
    <script type="text/javascript">
        function removeFromCartView(e, key) {
            e.preventDefault();
            removeFromCart(key);
        }

        function updateQuantity(key, element) {
            $.post('{{ route('cart.updateQuantity') }}', {
                _token: AIZ.data.csrf,
                id: key,
                quantity: element.value
            }, function(data) {
                updateNavCart(data.nav_cart_view, data.cart_count);
                $('#cart-details').html(data.cart_view);
                AIZ.extra.plusMinus();
            });
        }

        // Cart item selection
        $(document).on("change", ".check-all", function() {
            $('.check-one:checkbox').prop('checked', this.checked);
            updateCartStatus();
        });
        $(document).on("change", ".check-seller", function() {
            var value = this.value;
            $('.check-one-'+value+':checkbox').prop('checked', this.checked);
            updateCartStatus();
        });
        $(document).on("change", ".check-one[name='id[]']", function(e) {
            e.preventDefault();
            updateCartStatus();
        });
        function updateCartStatus() {
            $('.aiz-refresh').addClass('active');
            let product_id = [];
            $(".check-one[name='id[]']:checked").each(function() {
                product_id.push($(this).val());
            });

            $.post('{{ route('cart.updateCartStatus') }}', {
                _token: AIZ.data.csrf,
                product_id: product_id
            }, function(data) {
                $('#cart-details').html(data);
                AIZ.extra.plusMinus();
                $('.aiz-refresh').removeClass('active');
            });
        }

        // coupon apply
        $(document).on("click", "#coupon-apply", function() {
            @if (Auth::check())
                @if(!isCustomer())
                    AIZ.plugins.notify('warning', "{{ translate('Please Login as a customer to apply coupon code.') }}");
                    return false;
                @endif

                var data = new FormData($('#apply-coupon-form')[0]);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('checkout.apply_coupon_code') }}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                        $("#cart_summary").html(data.html);
                    }
                });
            @else
                $('#login_modal').modal('show');
            @endif
        });

        // coupon remove
        $(document).on("click", "#coupon-remove", function() {
            @if (Auth::check() && isCustomer())
                var data = new FormData($('#remove-coupon-form')[0]);
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "POST",
                    url: "{{ route('checkout.remove_coupon_code') }}",
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data, textStatus, jqXHR) {
                        $("#cart_summary").html(data);
                    }
                });
            @endif
        });

        function quickAddToCart(id, el) {
            $(el).prop('disabled', true);
            $.post('{{ route('cart.addToCart') }}', {
                _token: AIZ.data.csrf,
                id: id,
                quantity: 1
            }, function(data) {
                if (data.status == 1) {
                    AIZ.plugins.notify('success', '{{ translate('Premium accent added to your cart') }}');
                    // Refresh cart details
                    updateCartStatus();
                    updateNavCart(data.nav_cart_view, data.cart_count);
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Could not add item to cart') }}');
                    $(el).prop('disabled', false);
                }
            });
        }

    </script>
@endsection
