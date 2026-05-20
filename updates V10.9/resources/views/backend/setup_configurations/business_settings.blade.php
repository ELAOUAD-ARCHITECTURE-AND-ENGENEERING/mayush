@extends('backend.layouts.app')

@section('content')
<div class="business-setting-wrapper pt-4">
    <div class="col-11 col-xxl-9 mx-auto">
        <h1 class="fs-18 fw-bold mb-0 mt-2 pt-1 pb-1">{{ translate('Business Settings') }}</h1>
        <span
            class="fs-12 fw-400">{{ translate('Manage core business operations including orders, invoicing and delivery') }}</span>

        <div class="row mt-3 gutters-12">
            <div class="col-lg-6">
                <a href="{{ route('general.info') }}" class="d-block">
                    <div class="card border border-2 border-gray-200 card-no-shadow has-transition rounded-2">
                        <div class="card-body">
                            <div
                                class="w-60px h-60px rounded-1 d-flex align-items-center justify-content-center overflow-hidden bg-soft-blue mb-3">
                                <img src="{{ static_asset('assets/img/business-settings/general-settings.svg') }}"
                                    alt="Setting Icon">
                            </div>
                            <h6 class="fw-semibold text-dark">{{translate('General Settings')}}</h6>
                            <span
                                class="fs-12 fw-400 d-block text-gray">{{translate('Manage core business information, store identity and essential configurations that define how your store operates.')}}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('order.config') }}" class="d-block">
                    <div class="card border border-2 border-gray-200 card-no-shadow has-transition rounded-2">
                        <div class="card-body">
                            <div
                                class="w-60px h-60px rounded-1 d-flex align-items-center justify-content-center overflow-hidden bg-soft-warning mb-3">
                                <img src="{{ static_asset('assets/img/business-settings/order-configuration.svg') }}"
                                    alt="Setting Icon">
                            </div>
                            <h6 class="fw-semibold text-dark">{{translate('Order Configuration')}}</h6>
                            <span
                                class="fs-12 fw-400 d-block text-gray">{{translate('Set up order processing rules to control how orders are placed, managed and fulfilled.')}}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('invoice.config') }}" class="d-block">
                    <div class="card border border-2 border-gray-200 card-no-shadow has-transition rounded-2">
                        <div class="card-body">
                            <div
                                class="w-60px h-60px rounded-1 d-flex align-items-center justify-content-center overflow-hidden mb-3 bg-soft-sky-blue">
                                <img src="{{ static_asset('assets/img/business-settings/invoice-setting.svg') }}"
                                    alt="Setting Icon">
                            </div>
                            <h6 class="fw-semibold text-dark">{{translate('Invoice Settings')}}</h6>
                            <span
                                class="fs-12 fw-400 d-block text-gray">{{translate('Configure invoice to ensure accurate and professional order documentation.')}}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('shipping_label.config') }}" class="d-block">
                    <div class="card border border-2 border-gray-200 card-no-shadow has-transition rounded-2">
                        <div class="card-body">
                            <div
                                class="w-60px h-60px rounded-1 d-flex align-items-center justify-content-center overflow-hidden mb-3 bg-soft-danger">
                                <img src="{{ static_asset('assets/img/business-settings/shipping-label.svg') }}"
                                    alt="Setting Icon">
                            </div>
                            <h6 class="fw-semibold text-dark">{{translate('Shipping Label')}}</h6>
                            <span
                                class="fs-12 fw-400 d-block text-gray">{{translate('Configure shipping label formats, layout and printing preferences for efficient order packaging and dispatch.')}}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('thermal_printer.config') }}" class="d-block">
                    <div class="card border border-2 border-gray-200 card-no-shadow has-transition rounded-2">
                        <div class="card-body">
                            <div
                                class="w-60px h-60px rounded-1 d-flex align-items-center justify-content-center overflow-hidden mb-3 bg-soft-purple">
                                <img src="{{ static_asset('assets/img/business-settings/thermal-printer.svg') }}"
                                    alt="Setting Icon">
                            </div>
                            <h6 class="fw-semibold text-dark">{{translate('Thermal Printer Settings')}}</h6>
                            <span
                                class="fs-12 fw-400 d-block text-gray">{{translate('Adjust printer settings and formats to ensure smooth and accurate printing of receipts, invoices and labels using thermal printers.')}}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection