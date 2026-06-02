<div class="metro-collection-subsection">
    <div class="metro-collection-copy">
        <div class="skeleton-shimmer h-30px w-75 mx-auto rounded"></div>
        <div class="skeleton-shimmer h-15px w-50 mt-3 mx-auto rounded"></div>
        <div class="skeleton-shimmer h-15px w-90px mt-3 mx-auto rounded"></div>
    </div>
    <div class="metro-collection-products">
        @for ($i = 0; $i < 3; $i++)
            <div class="metro-collection-product-slide">
                <div class="metro-collection-product">
                    <div class="metro-collection-product-link">
                        <div class="metro-collection-product-image skeleton-shimmer"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-shimmer h-15px w-100 rounded"></div>
                            <div class="skeleton-shimmer h-15px w-60px mt-2 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
