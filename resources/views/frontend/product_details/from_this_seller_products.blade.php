<div
    class="frequently-bought-container py-20px px-30px border bg-white border-light-gray rounded-2">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <p class="fs-20 fw-bold text-dark m-0">{{ translate('Products from this Seller') }}</p>
        <a type="button"
            class="arrow-next text-white bg-dark view-more-slide-btn d-flex align-items-center"
            href="#">
            <span><i class="las la-angle-right fs-20 fw-600"></i></span>
            <span class="fs-12 mr-2 text">{{ translate('View All') }}</span>
        </a>
    </div>

    <div class="aiz-carousel arrow-x-0 arrow-inactive-none" data-items="6" data-xxl-items="6"
        data-xl-items="6" data-lg-items="5" data-md-items="4" data-sm-items="4"
        data-xs-items="3" data-arrows="false" data-dots="false" data-autoplay="true"
        data-infinite="true">

        <!--Single-->
        @foreach (get_same_seller_products($detailedProduct->user_id , 20) as $key => $product)
        <div class="carousel-box px-3">
            @include('frontend.partials.product_box_1', ['product' => $product])
        </div>
        @endforeach
    </div>
</div>