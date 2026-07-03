@php
    $shareUrl = route('blog.details', $blog->slug);
    $shareTitle = $blog->getTranslation('title');
    $encodedUrl = rawurlencode($shareUrl);
    $encodedTitle = rawurlencode($shareTitle);
@endphp

<div class="mb-blog-share-bar d-flex flex-wrap align-items-center mb-4" data-share-url="{{ $shareUrl }}">
    <span class="fs-12 fw-700 text-uppercase mr-2">{{ translate('Share') }}</span>
    <a class="btn btn-sm btn-soft-success mr-2 mb-2" target="_blank" rel="noopener" href="https://wa.me/?text={{ $encodedTitle }}%20{{ $encodedUrl }}">WhatsApp</a>
    <a class="btn btn-sm btn-soft-primary mr-2 mb-2" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}">Facebook</a>
    <a class="btn btn-sm btn-soft-dark mr-2 mb-2" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $encodedUrl }}&text={{ $encodedTitle }}">X</a>
    <button type="button" class="btn btn-sm btn-soft-secondary mb-2" data-blog-copy-link>{{ translate('Copy link') }}</button>
    <span class="fs-12 text-success ml-2 d-none" data-blog-copy-success>{{ translate('Copied') }}</span>
</div>
