@php
    $toc = $toc ?? [];
@endphp

@if(!empty($toc))
    <nav class="mb-blog-toc p-3 border mb-4" aria-label="{{ translate('Table of contents') }}">
        <h3 class="fs-16 fw-700 text-dark mb-3">{{ translate('On this page') }}</h3>
        <ol class="list-unstyled mb-0">
            @foreach($toc as $item)
                <li class="{{ ($item['level'] ?? 2) === 3 ? 'ml-3' : '' }} mb-2">
                    <a href="#{{ $item['id'] }}" class="fs-14 text-reset hov-text-primary">
                        {{ $item['text'] }}
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
@endif
