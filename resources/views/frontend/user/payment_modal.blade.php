@php
    $methods = $methods ?? get_all_manual_payment_methods();
@endphp

@if ($methods->isEmpty())
    <div class="p-4 text-center">
        <i class="las la-university fs-28 text-teal" aria-hidden="true"></i>
        <h6 class="mt-3 mb-2">{{ translate('No bank transfer method is available') }}</h6>
        <p class="text-muted fs-13 mb-0">{{ translate('Please choose an online payment method or contact support.') }}</p>
    </div>
@else
    <form action="{{ route('purchase_history.make_payment') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <div class="modal-body">
            <p class="text-muted fs-13 mb-3">{{ translate('Select the transfer method and submit your proof of payment.') }}</p>

            <div class="payment-method-grid mb-4">
                @foreach ($methods as $method)
                    <label class="order-method">
                        <input value="manual_payment_{{ $method->id }}" type="radio" name="payment_option" onchange="toggleManualPaymentData({{ $method->id }})" data-id="{{ $method->id }}" {{ $loop->first ? 'checked' : '' }} required>
                        <span class="order-method__card">
                            <span class="order-method__logo"><img src="{{ uploaded_asset($method->photo) }}" alt="{{ $method->heading }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';"></span>
                            <span><span class="order-method__name">{{ $method->heading }}</span><span class="order-method__hint">{{ translate('Manual verification') }}</span></span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="order-payment-note mb-3" id="manual_payment_description" aria-live="polite"></div>

            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="fs-12 fw-700" for="manual-payment-amount">{{ translate('Amount') }}</label>
                    <input id="manual-payment-amount" type="text" class="form-control" value="{{ single_price($order->grand_total) }}" readonly>
                </div>
                <div class="col-md-6 form-group">
                    <label class="fs-12 fw-700" for="manual-payment-name">{{ translate('Account name') }} <span class="text-danger">*</span></label>
                    <input id="manual-payment-name" type="text" class="form-control" name="name" value="{{ auth()->user()->name }}" required>
                </div>
                <div class="col-12 form-group">
                    <label class="fs-12 fw-700" for="manual-payment-trx">{{ translate('Transaction ID') }} <span class="text-danger">*</span></label>
                    <input id="manual-payment-trx" type="text" class="form-control" name="trx_id" placeholder="{{ translate('Enter the transfer reference') }}" required>
                </div>
                <div class="col-12 form-group">
                    <label class="fs-12 fw-700">{{ translate('Payment screenshot') }}</label>
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend"><div class="input-group-text bg-white">{{ translate('Browse') }}</div></div>
                        <div class="form-control file-amount">{{ translate('Choose image') }}</div>
                        <input type="hidden" name="photo" class="selected-files">
                    </div>
                    <div class="file-preview box sm"></div>
                </div>
            </div>

            <div class="order-modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ translate('Submit proof') }} <i class="las la-arrow-right ml-1" aria-hidden="true"></i></button>
            </div>
        </div>
    </form>

    @foreach ($methods as $method)
        <div id="manual_payment_info_{{ $method->id }}" class="d-none">
            @php echo $method->description @endphp
            @if ($method->bank_info)
                @php $bankInfo = is_string($method->bank_info) ? json_decode($method->bank_info, true) : $method->bank_info; @endphp
                @if (is_iterable($bankInfo))
                    <ul class="mb-0 pl-3">
                        @foreach ($bankInfo as $info)
                            <li>{{ translate('Bank') }}: {{ data_get($info, 'bank_name') }}, {{ translate('Account') }}: {{ data_get($info, 'account_name') }}, {{ translate('Account number') }}: {{ data_get($info, 'account_number') }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    @endforeach

    <script>
        (function () {
            window.toggleManualPaymentData = function (id) {
                var source = document.getElementById('manual_payment_info_' + id);
                var target = document.getElementById('manual_payment_description');
                if (source && target) target.innerHTML = source.innerHTML;
            };
            var selected = document.querySelector('#offline_order_re_payment_modal_body input[name="payment_option"]:checked');
            if (selected) window.toggleManualPaymentData(selected.dataset.id);
        }());
    </script>
@endif
