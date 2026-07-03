@if(!empty($blocks))
    @foreach($blocks as $block)
        @php
            $type = $block['type'] ?? '';
            $data = $block['data'] ?? [];
        @endphp

        @if($type === 'heading')
            @php $level = in_array((int)($data['level'] ?? 2), [2, 3, 4]) ? (int)$data['level'] : 2; @endphp
            <h{{ $level }} class="mt-4 mb-3">{{ $data['text'] ?? '' }}</h{{ $level }}>
        
        @elseif($type === 'paragraph')
            <p>{!! nl2br(e($data['text'] ?? '')) !!}</p>
            
        @elseif($type === 'rich_text')
            <div class="rich-text mb-4">
                {!! $data['text'] ?? '' !!}
            </div>
            
        @elseif($type === 'html')
            <div class="raw-html-block mb-4">
                {!! $data['code'] ?? '' !!}
            </div>

        @elseif($type === 'image')
            @if(!empty($data['upload_id']))
                <figure class="mb-4 text-center">
                    <img src="{{ uploaded_asset($data['upload_id']) }}" alt="{{ $data['alt'] ?? '' }}" class="img-fluid rounded lazyload" loading="lazy">
                    @if(!empty($data['caption']))
                        <figcaption class="text-muted mt-2 fs-13">{{ $data['caption'] }}</figcaption>
                    @endif
                </figure>
            @endif

        @elseif($type === 'gallery')
            @php
                $uploadIds = array_filter(explode(',', $data['upload_ids'] ?? ''));
            @endphp
            @if(!empty($uploadIds))
                <div class="row gutters-10 mb-4">
                    @foreach($uploadIds as $id)
                        <div class="col-6 col-sm-4 mb-3">
                            <img src="{{ uploaded_asset($id) }}" class="img-fluid rounded lazyload w-100 h-100" style="object-fit: cover; min-height: 150px;" loading="lazy">
                        </div>
                    @endforeach
                </div>
            @endif

        @elseif($type === 'quote')
            <blockquote class="blockquote border-left border-primary pl-4 py-2 mb-4 bg-light">
                <p class="mb-0 font-italic">{{ $data['text'] ?? '' }}</p>
                @if(!empty($data['cite']))
                    <footer class="blockquote-footer mt-2">{{ $data['cite'] }}</footer>
                @endif
            </blockquote>

        @elseif($type === 'list')
            @php $tag = ($data['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul'; @endphp
            <{{ $tag }} class="mb-4">
                @foreach($data['items'] ?? [] as $item)
                    <li>{{ $item }}</li>
                @endforeach
            </{{ $tag }}>

        @elseif($type === 'faq')
            <div class="accordion mb-4" id="faqAccordion-{{ $loop->index }}">
                @foreach($data['items'] ?? [] as $index => $item)
                    <div class="card shadow-none border mb-2">
                        <div class="card-header p-0" id="faqHeading-{{ $loop->parent->index }}-{{ $index }}">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-dark font-weight-bold text-decoration-none px-3 py-3" type="button" data-toggle="collapse" data-target="#faqCollapse-{{ $loop->parent->index }}-{{ $index }}" aria-expanded="true" aria-controls="faqCollapse-{{ $loop->parent->index }}-{{ $index }}">
                                    {{ $item['question'] ?? '' }}
                                </button>
                            </h2>
                        </div>
                        <div id="faqCollapse-{{ $loop->parent->index }}-{{ $index }}" class="collapse" aria-labelledby="faqHeading-{{ $loop->parent->index }}-{{ $index }}" data-parent="#faqAccordion-{{ $loop->parent->index }}">
                            <div class="card-body px-3 py-3 text-muted">
                                {!! nl2br(e($item['answer'] ?? '')) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($type === 'product_recommendation')
            @php
                $productIds = array_filter(explode(',', $data['product_ids'] ?? ''));
                $products = !empty($productIds) ? \App\Models\Product::whereIn('id', $productIds)->get() : collect();
            @endphp
            @if($products->count() > 0)
                <div class="my-5 border rounded p-4 bg-light">
                    <h3 class="fs-20 fw-700 mb-4">{{ $data['title'] ?? translate('Recommended Products') }}</h3>
                    <div class="row gutters-10">
                        @foreach($products as $product)
                            <div class="col-sm-6 col-md-3 mb-3">
                                @include('frontend.partials.product_box_1', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @elseif($type === 'shop_highlight')
            @php
                $shop = !empty($data['shop_id']) ? \App\Models\Shop::find($data['shop_id']) : null;
            @endphp
            @if($shop)
                <div class="my-5 border rounded p-4 d-flex align-items-center bg-white shadow-sm">
                    <img src="{{ uploaded_asset($shop->logo) }}" alt="{{ $shop->name }}" class="size-80px rounded-circle border mr-4" style="object-fit: cover;">
                    <div>
                        <h4 class="fs-18 fw-700 mb-1">{{ $shop->name }}</h4>
                        <p class="fs-13 text-muted mb-2 text-truncate-2">{{ strip_tags($shop->about) }}</p>
                        <a href="{{ route('shop.visit', $shop->slug) }}" class="btn btn-sm btn-primary">{{ translate('Visit Store') }}</a>
                    </div>
                </div>
            @endif

        @elseif($type === 'divider')
            <hr class="my-5">

        @endif
    @endforeach
@else
    {!! $fallbackDescription ?? '' !!}
@endif
