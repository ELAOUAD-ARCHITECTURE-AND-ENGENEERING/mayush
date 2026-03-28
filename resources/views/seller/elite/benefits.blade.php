@extends('seller.layouts.app')

@section('panel_content')
<div class="row">
    <div class="col-lg-12">
        {{-- Hero Section --}}
        <div class="card border-0 shadow-lg overflow-hidden mb-4" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);">
            <div class="card-body text-center py-5 px-4">
                <div class="mb-3">
                    <span style="font-size: 64px; filter: drop-shadow(0 0 20px rgba(241, 196, 15, 0.5));">
                        <i class="las la-crown" style="color: #f1c40f;"></i>
                    </span>
                </div>
                <h2 class="text-white fw-700 mb-3" style="font-size: 2rem;">
                    {{translate('Become an Elite Artisan')}}
                </h2>
                <p class="text-white-50 fs-16 mb-4 mx-auto" style="max-width: 600px;">
                    {{translate('Join an exclusive community of top sellers. Unlock premium visibility, priority placement, and powerful tools to grow your business.')}}
                </p>
                <a href="{{ route('seller.elite.pricing') }}" class="btn btn-lg px-5 py-3 fw-700"
                   style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; border: none; border-radius: 50px; box-shadow: 0 4px 15px rgba(241, 196, 15, 0.4); transition: all 0.3s ease;"
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(241, 196, 15, 0.6)'"
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(241, 196, 15, 0.4)'">
                    <i class="las la-rocket mr-1"></i> {{translate('Apply for Elite Artisan')}}
                </a>
            </div>
        </div>

        {{-- Benefits Section --}}
        <div class="mb-4">
            <h4 class="fw-700 mb-3 text-center"><i class="las la-gem text-warning"></i> {{translate('Elite Artisan Benefits')}}</h4>
            <div class="row gutters-10">
                {{-- Benefit 1 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #f1c40f !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(241, 196, 15, 0.1);">
                                    <i class="las la-eye fs-24" style="color: #f1c40f;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Increased Visibility')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Your products and shop appear more prominently across the marketplace, helping you reach more potential buyers organically.')}}
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Benefit 2 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #e74c3c !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(231, 76, 60, 0.1);">
                                    <i class="las la-search-plus fs-24" style="color: #e74c3c;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Premium Search Placement')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Your listings are boosted to the top of search results, giving you a competitive edge and maximizing your product exposure.')}}
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Benefit 3 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #3498db !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(52, 152, 219, 0.1);">
                                    <i class="las la-users fs-24" style="color: #3498db;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Exclusive Buyer Segments')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Access premium buyer audiences who actively seek high-quality, curated products from trusted artisan sellers.')}}
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Benefit 4 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #9b59b6 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(155, 89, 182, 0.1);">
                                    <i class="las la-certificate fs-24" style="color: #9b59b6;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Enhanced Profile Badges')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Stand out with an exclusive Elite badge on your shop profile and product listings, building instant trust with buyers.')}}
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Benefit 5 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #2ecc71 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(46, 204, 113, 0.1);">
                                    <i class="las la-headset fs-24" style="color: #2ecc71;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Priority Customer Support')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Get faster response times and dedicated support from our team to resolve issues quickly and keep your shop running smoothly.')}}
                            </p>
                        </div>
                    </div>
                </div>
                {{-- Benefit 6 --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #e67e22 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
                                     style="width: 48px; height: 48px; background: rgba(230, 126, 34, 0.1);">
                                    <i class="las la-chart-line fs-24" style="color: #e67e22;"></i>
                                </div>
                                <h6 class="mb-0 fw-700">{{translate('Higher Conversion Rates')}}</h6>
                            </div>
                            <p class="text-muted fs-13 mb-0">
                                {{translate('Elite sellers see up to 2.5x higher conversion rates thanks to premium placement, trust badges, and curated showcasing.')}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Section --}}
        <div class="card border-0 shadow-sm mb-4" style="background: #f8f9fa;">
            <div class="card-body py-4">
                <h5 class="fw-700 text-center mb-4">{{translate('Why Sellers Love Elite Artisan')}}</h5>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="py-3">
                            <h2 class="fw-700 mb-1" style="color: #f1c40f;">40%</h2>
                            <p class="text-muted fs-13 mb-0">{{translate('More Visibility')}}</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="py-3">
                            <h2 class="fw-700 mb-1" style="color: #e74c3c;">2.5x</h2>
                            <p class="text-muted fs-13 mb-0">{{translate('Conversion Rate')}}</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="py-3">
                            <h2 class="fw-700 mb-1" style="color: #3498db;">85%</h2>
                            <p class="text-muted fs-13 mb-0">{{translate('Seller Satisfaction')}}</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="py-3">
                            <h2 class="fw-700 mb-1" style="color: #2ecc71;">+60%</h2>
                            <p class="text-muted fs-13 mb-0">{{translate('Revenue Growth')}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Stories --}}
        <div class="mb-4">
            <h5 class="fw-700 text-center mb-3"><i class="las la-quote-left text-warning"></i> {{translate('Seller Success Stories')}}</h5>
            <div class="row gutters-10">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #f1c40f, #f39c12);">
                                    <i class="las la-store-alt text-white fs-28"></i>
                                </div>
                            </div>
                            <p class="fs-13 text-muted mb-2" style="font-style: italic;">
                                "{{translate('Since becoming an Elite Artisan, my sales have increased by 70%. The premium placement really makes a difference!')}}"
                            </p>
                            <p class="fw-600 fs-13 mb-0">— {{translate('Boutique Artisanale Fès')}}</p>
                            <small class="text-warning"><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #3498db, #2980b9);">
                                    <i class="las la-paint-brush text-white fs-28"></i>
                                </div>
                            </div>
                            <p class="fs-13 text-muted mb-2" style="font-style: italic;">
                                "{{translate('The Elite badge gave my shop instant credibility. Customers now trust my products more and my reviews skyrocketed.')}}"
                            </p>
                            <p class="fw-600 fs-13 mb-0">— {{translate('Maison du Cuir Marrakech')}}</p>
                            <small class="text-warning"><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                                     style="width: 60px; height: 60px; background: linear-gradient(135deg, #2ecc71, #27ae60);">
                                    <i class="las la-gem text-white fs-28"></i>
                                </div>
                            </div>
                            <p class="fs-13 text-muted mb-2" style="font-style: italic;">
                                "{{translate('Priority support saved me during a critical order period. The Elite team responded within minutes. Worth every dirham!')}}"
                            </p>
                            <p class="fw-600 fs-13 mb-0">— {{translate('Tapis Berbère Essaouira')}}</p>
                            <small class="text-warning"><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star"></i><i class="las la-star-half-alt"></i></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom CTA --}}
        <div class="text-center mb-4">
            <a href="{{ route('seller.elite.pricing') }}" class="btn btn-lg px-5 py-3 fw-700"
               style="background: linear-gradient(135deg, #f1c40f, #f39c12); color: #1a1a2e; border: none; border-radius: 50px; box-shadow: 0 4px 15px rgba(241, 196, 15, 0.4);">
                <i class="las la-crown mr-1"></i> {{translate('View Plans & Pricing')}}
            </a>
        </div>
    </div>
</div>
@endsection
