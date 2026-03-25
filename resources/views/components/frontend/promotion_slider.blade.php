<style>
    .promotion-slider-container {
        position: relative;
        width: 100%;
        overflow: hidden;
    }
    .promotion-slider-wrapper {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
        gap: 16px;
        padding: 10px 0;
        scrollbar-width: none;
    }
    .promotion-slider-wrapper::-webkit-scrollbar {
        display: none;
    }
    .promotion-slide {
        flex: 0 0 auto;
        scroll-snap-align: start;
        width: 240px;
    }
    
    /* Mobile: 2-row masonry grid (Vertical) */
    @media (max-width: 768px) {
        .promotion-slider-wrapper {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            overflow-x: hidden;
            overflow-y: auto;
            scroll-snap-type: none;
            padding: 0;
        }
        .promotion-slide {
            width: auto;
        }
        .slider-nav-btn {
            display: none !important;
        }
    }

    .slider-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .promotion-slider-container:hover .slider-nav-btn {
        opacity: 1;
    }
    .slider-prev { left: 10px; }
    .slider-next { right: 10px; }
</style>

<div class="promotion-slider-container" id="promotionSliderContainer">
    <button class="slider-nav-btn slider-prev" id="promoPrev" aria-label="Previous">
        <i class="las la-angle-left fs-20"></i>
    </button>
    
    <div class="promotion-slider-wrapper" id="promotionSliderWrapper">
        @foreach($products as $product)
            <div class="promotion-slide">
                <div class="aiz-card-box p-2 h-100 bg-white border rounded shadow-sm has-transition hov-shadow-out">
                    <div class="position-relative">
                        <a href="{{ route('customer.product', $product->slug) }}" class="d-block text-center">
                            <img class="img-fit h-140px h-md-200px lazyload" 
                                src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                data-src="{{ get_image($product->thumbnail, 'medium') }}"
                                alt="{{ $product->getTranslation('name') }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                        </a>
                        @if($product->conditon == 'new')
                            <span class="absolute-top-left bg-info text-white fs-10 px-2 py-1 rounded">{{ translate('New') }}</span>
                        @elseif($product->conditon == 'used')
                            <span class="absolute-top-left bg-danger text-white fs-10 px-2 py-1 rounded">{{ translate('Used') }}</span>
                        @endif
                    </div>
                    <div class="p-2 text-left">
                        <h3 class="fw-600 fs-14 text-truncate-2 lh-1-4 mb-0 h-35px">
                            <a href="{{ route('customer.product', $product->slug) }}" class="text-reset hov-text-primary">
                                {{ $product->getTranslation('name') }}
                            </a>
                        </h3>
                        <div class="fs-14 mt-2">
                            <span class="fw-700 text-primary">{{ single_price($product->unit_price) }}</span>
                        </div>
                        <div class="fs-12 text-secondary text-truncate mt-1">
                            {{ $product->user ? $product->user->name : '' }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button class="slider-nav-btn slider-next" id="promoNext" aria-label="Next">
        <i class="las la-angle-right fs-20"></i>
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wrapper = document.getElementById('promotionSliderWrapper');
        const prevBtn = document.getElementById('promoPrev');
        const nextBtn = document.getElementById('promoNext');
        
        // Desktop Horizontal Scroll Logic
        if(window.innerWidth > 768) {
            // Drag Scrolling
            let isDown = false;
            let startX;
            let scrollLeft;

            wrapper.addEventListener('mousedown', (e) => {
                isDown = true;
                wrapper.classList.add('active');
                startX = e.pageX - wrapper.offsetLeft;
                scrollLeft = wrapper.scrollLeft;
            });
            wrapper.addEventListener('mouseleave', () => {
                isDown = false;
                wrapper.classList.remove('active');
            });
            wrapper.addEventListener('mouseup', () => {
                isDown = false;
                wrapper.classList.remove('active');
            });
            wrapper.addEventListener('mousemove', (e) => {
                if(!isDown) return;
                e.preventDefault();
                const x = e.pageX - wrapper.offsetLeft;
                const walk = (x - startX) * 2; // scroll-fast
                requestAnimationFrame(() => {
                    wrapper.scrollLeft = scrollLeft - walk;
                });
            });

            // Buttons
            prevBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: -300, behavior: 'smooth' });
            });
            nextBtn.addEventListener('click', () => {
                wrapper.scrollBy({ left: 300, behavior: 'smooth' });
            });

            // Keyboard
            wrapper.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') wrapper.scrollBy({ left: -300, behavior: 'smooth' });
                if (e.key === 'ArrowRight') wrapper.scrollBy({ left: 300, behavior: 'smooth' });
            });
        }
    });
</script>
