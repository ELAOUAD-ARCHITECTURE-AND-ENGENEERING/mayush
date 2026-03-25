@if (count($hot_categories) > 0)
    <section class="mb-2 mb-md-3 mt-2 mt-md-3">
        <div class="container">
            <div class="d-flex mb-2 mb-md-3 align-items-baseline justify-content-between">
                <h3 class="fs-16 fs-md-20 fw-700 mb-2 mb-sm-0">
                    <span>{{ translate('Category Navigation') }}</span>
                </h3>
            </div>
            <div class="bg-white px-3 py-3 border">
                <div class="aiz-carousel arrow-inactive-transparent arrow-x-0"
                    data-rows="1" data-items="8" data-xxl-items="8" data-xl-items="7" data-lg-items="6"
                    data-md-items="5" data-sm-items="4" data-xs-items="3" data-arrows="true" data-dots="false" data-autoplay="true" data-infinite="true">
                
                    @foreach ($hot_categories as $key => $category)
                    @php
                        $category_name = $category->getTranslation('name');
                    @endphp
                    <div class="carousel-box text-center px-2">
                        <a href="{{ route('products.category', $category->slug) }}" class="d-block text-reset">
                            <div class="img size-60px size-lg-80px mx-auto rounded-circle overflow-hidden border mb-2 hov-scale-img has-transition">
                                <img class="lazyload img-fit"
                                    src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                    data-src="{{ isset($category->banner) ? uploaded_asset($category->banner, 'thumb') : static_asset('assets/img/placeholder.jpg') }}"
                                    alt="{{ $category_name }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                            </div>
                            <div class="fs-12 fw-700 text-truncate-2 h-30px" title="{{ $category_name }}">
                                {{ $category_name }}
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
