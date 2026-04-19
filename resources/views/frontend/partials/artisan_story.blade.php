<section class="artisan-story py-5" id="artisan-story-section">
    <div class="container">
        <div class="row align-items-center">
            @if($shop->hero_media_id)
            <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right" data-aos-delay="100">
                <div class="position-relative overflow-hidden rounded-lg shadow-xl artisan-hero-container artisan-glass">
                    <img src="{{ uploaded_asset($shop->hero_media_id) }}" 
                         class="img-fluid w-100 artisan-hero-img" 
                         alt="{{ $shop->story_title ?? $shop->name }}"
                         loading="lazy">
                    <div class="artisan-badge px-3 py-2">
                        <span class="fs-12 fw-700 text-uppercase tracking-wider">{{ translate('Meet the Artisan') }}</span>
                    </div>
                </div>
            </div>
            @endif
            
            <div class="{{ $shop->hero_media_id ? 'col-lg-6' : 'col-lg-10 mx-auto text-center' }} pl-lg-5" data-aos="fade-left" data-aos-delay="200">
                <div class="section-tag mb-2">
                    <span class="text-primary fw-600 fs-14">{{ translate('Our Heritage') }}</span>
                </div>
                <h2 class="h2 fw-800 text-dark mb-4 artisan-title">
                    {{ $shop->story_title ?? translate('The Art of Craftsmanship') }}
                </h2>
                
                <div class="artisan-content mb-4 text-secondary lh-1-8 fs-16">
                    @if($shop->story_content)
                        {!! $shop->story_content !!}
                    @else
                        <p>{{ $shop->artisan_story ?? translate('Welcome to our workshop. We pour our passion and history into every piece we create, preserving ancient techniques for the modern world.') }}</p>
                    @endif
                </div>

                @if($shop->artisan_quote)
                <div class="artisan-quote-container my-4 text-center p-4">
                    <p class="artisan-quote italic fs-20 text-dark mb-0">"{{ $shop->artisan_quote }}"</p>
                </div>
                @endif

                @if($shop->brand_philosophy)
                <div class="philosophy-card p-4 rounded-lg bg-soft-primary border-left-4 border-primary mb-4">
                    <h5 class="h6 fw-700 text-primary mb-2 italic-header"><i class="las la-quote-left mr-2"></i>{{ translate('Our Philosophy') }}</h5>
                    <p class="mb-0 fs-15 italic">{{ $shop->brand_philosophy }}</p>
                </div>
                @endif

                @if($shop->workshop_video_url)
                <div class="mt-4">
                    <a href="{{ $shop->workshop_video_url }}" class="btn btn-primary btn-md rounded-pill px-4 shadow-primary video-popup-btn">
                        <i class="las la-play-circle mr-2 fs-18"></i>{{ translate('Watch the Workshop') }}
                    </a>
                </div>
                @endif
            </div>
        </div>

        @if(!empty($shop->gallery_json) && is_array($shop->gallery_json))
        <div class="row mt-5 pt-4">
            <div class="col-12 text-center mb-4" data-aos="fade-up">
                <h3 class="h4 fw-700">{{ translate('From the Workshop') }}</h3>
                <div class="divider mx-auto" style="width: 50px; height: 3px; background: var(--primary);"></div>
            </div>
            <div class="col-12" data-aos="fade-up" data-aos-delay="100">
                <div class="aiz-carousel gutters-10" data-items="4" data-lg-items="3" data-md-items="2" data-sm-items="1" data-arrows="true" data-infinite="true">
                    @foreach($shop->gallery_json as $img_id)
                        <div class="carousel-box">
                            <div class="artisan-gallery-item rounded-lg overflow-hidden shadow-sm hov-shadow-md transition-all">
                                <img src="{{ uploaded_asset($img_id) }}" class="img-fit h-250px lazyload" alt="{{ translate('Process image') }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</section>

<style>
    .artisan-story {
        background: #fdfdfd;
        border-top: 1px solid #f0f0f0;
    }
    .artisan-title {
        font-family: 'Outfit', sans-serif;
        color: #1a1a1a;
        letter-spacing: -0.02em;
    }
    .artisan-hero-container {
        aspect-ratio: 4/5;
        background: #eee;
    }
    .artisan-hero-img {
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .artisan-hero-container:hover .artisan-hero-img {
        transform: scale(1.05);
    }
    .artisan-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        border-radius: 4px;
        color: var(--primary);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .philosophy-card {
        transition: all 0.3s ease;
    }
    .philosophy-card:hover {
        transform: translateX(5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .lh-1-8 {
        line-height: 1.8;
    }
    .border-left-4 {
        border-left: 4px solid;
    }
    .italic {
        font-style: italic;
    }
    .artisan-quote {
        font-family: 'Playfair Display', serif;
        border-bottom: 2px solid #eee;
        padding-bottom: 15px;
        display: inline-block;
    }
    .artisan-gallery-item:hover {
        transform: translateY(-5px);
    }
    .h-250px {
        height: 250px;
    }
</style>
