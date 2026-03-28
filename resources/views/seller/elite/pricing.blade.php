@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-12">
        {{-- Step Indicator --}}
        <div class="d-flex align-items-center justify-content-center mb-4">
            <div class="d-flex align-items-center">
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #f1c40f; width: 30px; height: 30px; line-height: 30px;">1</span>
                <span class="fw-600 text-muted mr-3">{{translate('Benefits')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #f1c40f; width: 30px; height: 30px; line-height: 30px;">2</span>
                <span class="fw-700 text-dark mr-3">{{translate('Plans')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #ccc; width: 30px; height: 30px; line-height: 30px;">3</span>
                <span class="text-muted mr-3">{{translate('Recap')}}</span>
                <i class="las la-chevron-right text-muted mr-3"></i>
                <span class="badge badge-circle text-white fw-700 mr-2" style="background: #ccc; width: 30px; height: 30px; line-height: 30px;">4</span>
                <span class="text-muted">{{translate('Payment')}}</span>
            </div>
        </div>

        <h4 class="fw-700 text-center mb-1"><i class="las la-crown text-warning"></i> {{translate('Choose Your Plan')}}</h4>
        <p class="text-center text-muted mb-4">{{translate('Select the subscription plan that best fits your business needs.')}}</p>

        <div class="row justify-content-center">
            {{-- Monthly Plan --}}
            <div class="col-lg-5 col-md-6 mb-4">
                <div class="card h-100 border shadow-sm" id="plan-monthly" style="transition: all 0.3s ease; cursor: pointer;"
                     onclick="selectPlan('monthly')">
                    <div class="card-header text-center py-3" style="background: #f8f9fa;">
                        <h5 class="mb-0 fw-700">{{translate('Monthly Plan')}}</h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="mb-3">
                            <span class="fs-14 text-muted">MAD</span>
                            <span class="fw-700" style="font-size: 3rem; color: #1a1a2e;">{{ number_format($monthly_price, 2) }}</span>
                            <span class="text-muted fs-14">/ {{translate('month')}}</span>
                        </div>
                        <hr>
                        <ul class="list-unstyled text-left px-3">
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Increased Visibility')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Premium Search Placement')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Elite Profile Badge')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Priority Customer Support')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Artisan Story Profile')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Exclusive Buyer Segments')}}</li>
                            <li class="mb-2 text-muted"><i class="las la-times-circle mr-2"></i> {{translate('Annual discount')}}</li>
                        </ul>
                    </div>
                    <div class="card-footer text-center py-3" style="background: #fff;">
                        <form action="{{ route('seller.elite.recap') }}" method="POST" id="form-monthly">
                            @csrf
                            <input type="hidden" name="billing_cycle" value="monthly">
                            <button type="submit" class="btn btn-outline-dark fw-600 px-4 py-2" id="btn-monthly">
                                {{translate('Select Monthly')}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Yearly Plan --}}
            <div class="col-lg-5 col-md-6 mb-4">
                <div class="card h-100 border-0 shadow-lg position-relative" id="plan-yearly"
                     style="transition: all 0.3s ease; cursor: pointer; border: 2px solid #f1c40f !important;"
                     onclick="selectPlan('yearly')">
                    {{-- Popular Badge --}}
                    <div class="position-absolute" style="top: -12px; right: 20px; z-index: 2;">
                        <span class="badge px-3 py-2 fw-700" style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; font-size: 11px; border-radius: 20px;">
                            <i class="las la-fire mr-1"></i> {{translate('BEST VALUE')}}
                        </span>
                    </div>
                    <div class="card-header text-center text-white py-3" style="background: linear-gradient(135deg, #1a1a2e, #0f3460);">
                        <h5 class="mb-0 fw-700">{{translate('Yearly Plan')}}</h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="mb-2">
                            <span class="fs-14 text-muted">MAD</span>
                            <span class="fw-700" style="font-size: 3rem; color: #1a1a2e;">{{ number_format($yearly_price, 2) }}</span>
                            <span class="text-muted fs-14">/ {{translate('year')}}</span>
                        </div>
                        @if($yearly_savings > 0)
                        <div class="mb-3">
                            <span class="badge px-3 py-1" style="background: #e8f5e9; color: #2e7d32; font-size: 12px;">
                                <i class="las la-tag mr-1"></i> {{translate('Save')}} {{ single_price($yearly_savings) }} ({{ $yearly_discount_pct }}% {{translate('off')}})
                            </span>
                        </div>
                        @endif
                        <hr>
                        <ul class="list-unstyled text-left px-3">
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Increased Visibility')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Premium Search Placement')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Elite Profile Badge')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Priority Customer Support')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Artisan Story Profile')}}</li>
                            <li class="mb-2"><i class="las la-check-circle text-success mr-2"></i> {{translate('Exclusive Buyer Segments')}}</li>
                            <li class="mb-2 fw-600" style="color: #2e7d32;"><i class="las la-check-circle mr-2"></i> {{translate('Annual discount included')}}</li>
                        </ul>
                    </div>
                    <div class="card-footer text-center py-3" style="background: #fff;">
                        <form action="{{ route('seller.elite.recap') }}" method="POST" id="form-yearly">
                            @csrf
                            <input type="hidden" name="billing_cycle" value="yearly">
                            <button type="submit" class="btn fw-600 px-4 py-2 text-dark" id="btn-yearly"
                                    style="background: linear-gradient(135deg, #f1c40f, #f39c12); border: none;">
                                {{translate('Select Yearly')}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature Comparison --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-700"><i class="las la-list-ul mr-1"></i> {{translate('Feature Comparison')}}</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th class="fw-600">{{translate('Feature')}}</th>
                            <th class="text-center fw-600">{{translate('Monthly')}}</th>
                            <th class="text-center fw-600" style="background: #fffef0;">{{translate('Yearly')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{translate('Homepage Premium Showcase')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr>
                            <td>{{translate('Search Results Boost')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr>
                            <td>{{translate('Elite Profile Badge')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr>
                            <td>{{translate('Artisan Story Page')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr>
                            <td>{{translate('Priority Support')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr>
                            <td>{{translate('Ad-Free Storefront')}}</td>
                            <td class="text-center"><i class="las la-check text-success fs-20"></i></td>
                            <td class="text-center" style="background: #fffef0;"><i class="las la-check text-success fs-20"></i></td>
                        </tr>
                        <tr class="font-weight-bold">
                            <td>{{translate('Savings vs Monthly Billing')}}</td>
                            <td class="text-center">—</td>
                            <td class="text-center" style="background: #e8f5e9; color: #2e7d32;">
                                {{ single_price($yearly_savings) }} ({{ $yearly_discount_pct }}%)
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mb-3">
            <a href="{{ route('seller.elite.index') }}" class="btn btn-link text-muted">
                <i class="las la-arrow-left mr-1"></i> {{translate('Back to Benefits')}}
            </a>
        </div>
    </div>
</div>

<script>
    function selectPlan(plan) {
        if (plan === 'monthly') {
            document.getElementById('plan-monthly').style.border = '2px solid #f1c40f';
            document.getElementById('plan-monthly').style.boxShadow = '0 8px 25px rgba(241, 196, 15, 0.2)';
            document.getElementById('plan-yearly').style.border = '2px solid #f1c40f';
            document.getElementById('plan-yearly').style.boxShadow = 'none';
        } else {
            document.getElementById('plan-yearly').style.border = '2px solid #f1c40f';
            document.getElementById('plan-yearly').style.boxShadow = '0 8px 25px rgba(241, 196, 15, 0.2)';
            document.getElementById('plan-monthly').style.border = '1px solid #dee2e6';
            document.getElementById('plan-monthly').style.boxShadow = 'none';
        }
    }
</script>
@endsection
