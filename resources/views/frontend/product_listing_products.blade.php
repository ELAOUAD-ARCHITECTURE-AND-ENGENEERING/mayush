@forelse ($products as $key => $product)
    <div class="col border-right border-bottom has-transition hov-shadow-out z-1 @if(isset($is_ai_mode) && $is_ai_mode) ai-result-card @endif">
        @if (isset($product_type) && $product_type == 'preorder_product')
            @include('preorder.frontend.product_box3', [
                'product' => $product,
            ])
        @else
            @include(
                'frontend.product_box_for_listing_page',
                [
                    'product' => $product,
                    'score' => $semantic_scores[$product->id] ?? null,
                    'is_ai_mode' => $is_ai_mode ?? false
                ]
            )
        @endif
    </div>
@empty
    <div class="col-12 border-right border-bottom py-5 text-center text-muted">
        {{ translate('No products found') }}
    </div>
@endforelse
