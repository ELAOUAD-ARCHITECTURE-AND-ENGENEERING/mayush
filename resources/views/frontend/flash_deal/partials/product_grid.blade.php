@foreach($flash_deal_products as $flash_deal_product)
    @if($flash_deal_product->product)
        <div class="col mb-3">
            @include('frontend.metro.partials.product_box_1', ['product' => $flash_deal_product->product])
        </div>
    @endif
@endforeach
