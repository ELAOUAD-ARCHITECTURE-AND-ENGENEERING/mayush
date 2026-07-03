 @forelse($orders as $order)

 <div class="mb-4">


     <div class="row align-items-center mb-3">


         <div class="col-md-12">
             @foreach($order->orderDetails as $orderDetail)
             @php
                 $product = $orderDetail->product;
                 $productName = $product ? $product->getTranslation('name') : translate('Product unavailable');
             @endphp

             @if($orderDetail->reviewed==0)
             @if (!$loop->first)
             <hr class="hr-split">
             @endif
             <div class="row">
                 <div class="col-md-7 d-flex align-items-center">
                     <img src="{{ $product ? uploaded_asset($product->thumbnail_img) : static_asset('assets/img/placeholder.jpg') }}"
                         class="img-fluid mr-3 product-history-img">

                     <div class="w-300px text-wrap">
                         <div class="font-weight-semibold fs-14 product-name-color mobile-title-shift text-truncate-2"
                             title="{{ $productName }}">
                             {{ $productName }}
                         </div>
                         <div class="text-muted small mb-2 mobile-title-shift">{{ $orderDetail->variation }}</div>
                     </div>

                 </div>

                 <div class="col-md-3">
                     <div class="font-weight-bold">{{ single_price($orderDetail->price) }}</div>
                     <div class="text-muted small">Qty {{ $orderDetail->quantity }}</div>
                 </div>

                 <div class="col-md-2">
                     <a href="javascript:void(0);"
                         onclick="product_review('{{ $orderDetail->product_id }}', '{{ $order->id }}')"
                         class="btn btn-primary btn-sm rounded-pill"> {{ translate('Review') }} </a>
                 </div>

             </div>

             <hr class="hr-split">
             <hr>
             @endif


             @endforeach
         </div>
     </div>


 </div>

 @empty
 <div class="text-center p-4 text-muted" id="purchase-history-review-empty-state">
     {{ translate('No products are waiting for review.') }}
 </div>
 @endforelse
