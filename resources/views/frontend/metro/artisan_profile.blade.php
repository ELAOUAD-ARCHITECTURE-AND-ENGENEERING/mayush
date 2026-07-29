@extends('frontend.layouts.app')

@section('content')
<div class="artisan-profile-wrapper">
    <!-- Hero Banner Full Bleed -->
    <div class="artisan-hero position-relative">
        <div class="hero-bg" style="background-image: url('{{ uploaded_asset($shop->top_banner_image) }}'); background-size: cover; background-position: center; min-height: 60vh;">
            <div class="hero-overlay position-absolute w-100 h-100" style="background: rgba(0,0,0,0.5); top:0; left:0;"></div>
        </div>
        <div class="position-absolute w-100 d-flex flex-column align-items-center justify-content-center text-center text-white" style="top: 50%; transform: translateY(-50%); z-index: 2;">
            <img src="{{ uploaded_asset($shop->logo) }}" alt="{{ $shop->name }}" class="rounded-circle shadow-lg mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #fff;">
            <h1 class="display-3 font-weight-bold" style="font-family: var(--mayush-font-heading);">{{ $shop->name }}</h1>
            <p class="lead mt-2">{{ $shop->meta_description }}</p>
        </div>
    </div>

    <!-- Philosophy Full Bleed -->
    @if($shop->brand_philosophy)
    <div class="philosophy-section text-center py-5" style="background-color: #f8f9fa;">
        <div class="container py-5">
            <h2 class="mb-4 text-uppercase text-secondary" style="letter-spacing: 2px;">{{ translate('Our Philosophy') }}</h2>
            <blockquote class="blockquote">
                <p class="mb-0 font-italic" style="font-size: 1.5rem; line-height: 1.8; color: #333; font-family: var(--mayush-font-heading);">
                    "{{ $shop->brand_philosophy }}"
                </p>
            </blockquote>
        </div>
    </div>
    @endif

    <!-- Story & Video -->
    <div class="container py-5 my-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                @if($shop->workshop_video_url)
                    <div class="embed-responsive embed-responsive-16by9 shadow-sm rounded overflow-hidden">
                        <iframe class="embed-responsive-item" src="{{ str_replace('watch?v=', 'embed/', $shop->workshop_video_url) }}" allowfullscreen></iframe>
                    </div>
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center shadow-sm rounded" style="min-height: 350px;">
                        <span class="text-muted">{{ translate('A Glimpse Into the Workshop') }}</span>
                    </div>
                @endif
            </div>
            <div class="col-lg-6 pl-lg-5">
                <h2 class="mb-4 font-weight-bold" style="font-size: 2.5rem; font-family: var(--mayush-font-heading);">{{ translate('The Artisan Story') }}</h2>
                <div class="text-muted mb-4" style="line-height: 1.8; font-size: 1.1rem;">
                    {!! nl2br(e($shop->artisan_story)) !!}
                </div>
                <hr class="w-25 ml-0 border-dark mb-4">
                <a href="#shop-products" class="btn btn-dark btn-lg px-5 rounded-0 text-uppercase" style="letter-spacing: 1px;">{{ translate('Explore Collection') }}</a>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div id="shop-products" class="container py-5">
        <div class="text-center mb-5">
            <h3 class="font-weight-bold" style="font-family: var(--mayush-font-heading);">{{ translate('Featured Works') }}</h3>
            <span class="text-muted">{{ translate('Handcrafted with passion') }}</span>
        </div>
        <div class="row gutters-10">
            @foreach($shop->user->products->where('published', 1)->take(12) as $product)
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">
                    <div class="product-card shadow-sm h-100 bg-white border border-light transition-transform">
                        <a href="{{ route('product', $product->slug) }}">
                            <img src="{{ uploaded_asset($product->thumbnail_img) }}" class="img-fluid w-100" alt="{{ $product->getTranslation('name') }}">
                        </a>
                        <div class="p-4 text-center">
                            <h5 class="text-truncate mb-2"><a href="{{ route('product', $product->slug) }}" class="text-dark">{{ $product->getTranslation('name') }}</a></h5>
                            <div class="font-weight-bold text-primary" style="font-size: 1.2rem;">{{ home_discounted_base_price($product) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
