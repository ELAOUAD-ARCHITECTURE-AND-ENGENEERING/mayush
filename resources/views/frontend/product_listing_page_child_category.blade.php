@php
    $value = null;
    for ($i=0; $i < $child_category->level; $i++){
        $value .= '-';
    }
@endphp

<li  d-item="{{ $child_category->products_count }}" id="generel_{{ $child_category->id }}">{{ $value }}
    {{ $child_category->getTranslation('name') }}
</li>

@if ($child_category->childrenCategories)
    @foreach ($child_category->childrenCategories as $childCategory)
        @include('frontend.product_listing_page_child_category', ['child_category' => $childCategory])
    @endforeach
@endif
