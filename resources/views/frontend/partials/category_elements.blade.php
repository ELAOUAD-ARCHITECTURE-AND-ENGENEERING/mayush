<div style="column-count: 3; column-gap: 30px;">
    @foreach ($categories->childrenCategories as $key => $category)
        <div class="mb-4" style="break-inside: avoid;">
            <ul class="list-unstyled mb-0">
                <li class="fs-14 fw-700 mb-3">
                    <a class="text-dark hov-text-primary" href="{{ route('products.category', $category->slug) }}">
                        {{ $category->getTranslation('name') }}
                    </a>
                </li>
                @if($category->childrenCategories->count())
                    @foreach ($category->childrenCategories as $key => $child_category)
                        <li class="mb-2 fs-14 pl-2">
                            <a class="text-dark hov-text-primary animate-underline-primary" href="{{ route('products.category', $child_category->slug) }}">
                                {{ $child_category->getTranslation('name') }}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>
    @endforeach
</div>
