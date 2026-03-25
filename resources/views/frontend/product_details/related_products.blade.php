<div class="related-product-container py-20px px-30px border bg-white border-light-gray rounded-2">
    <p class="fs-20 fw-bold text-dark">{{ translate('Related Products') }}</p>

    <div class="aiz-carousel arrow-x-0 arrow-inactive-none" data-items="6" data-xxl-items="6"
        data-xl-items="6" data-lg-items="5" data-md-items="4" data-sm-items="4" data-xs-items="3"
        data-arrows="false" data-dots="false" data-autoplay="true" data-infinite="true">

        <!--Single-->
        @foreach (get_related_products_by_category($detailedProduct->category_id) as $key => $product)
        <div class="carousel-box px-3">
            @include('frontend.partials.product_box_1', ['product' => $product])
        </div>
        @endforeach
    </div>
</div>