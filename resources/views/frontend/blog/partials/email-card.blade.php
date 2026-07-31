@php
    $placement = $placement ?? 'post_read';
    $headline = $headline ?? translate('Get Moroccan design ideas in your inbox');
    $text = $text ?? translate('Join Mayush readers for practical decor guides, buying tips, and marketplace picks.');
    $button = $button ?? translate('Subscribe');
    $blogId = isset($blog) && $blog ? $blog->id : null;
    $isSidebar = ($placement === 'sidebar');
@endphp

<div class="mb-blog-email-card {{ $isSidebar ? 'mb-blog-email-card--sidebar' : 'mb-blog-email-card--wide' }}">
    <div class="mb-blog-email-card__header">
        <div class="mb-blog-email-card__icon">
            <i class="las la-envelope-open-text"></i>
        </div>
        <div class="flex-grow-1">
            <h3 class="mb-blog-email-card__title">{{ $headline }}</h3>
            <p class="mb-blog-email-card__text">{{ $text }}</p>
        </div>
    </div>

    @if(session('blog_subscribe_success'))
        <div class="alert alert-success mb-0 mt-3 rounded-2 fs-13">{{ session('blog_subscribe_success') }}</div>
    @else
        <form method="POST" action="{{ route('blog.subscribe') }}" class="mb-blog-subscribe-form mt-3">
            @csrf
            <input type="hidden" name="placement" value="{{ $placement }}">
            @if($blogId)
                <input type="hidden" name="blog_id" value="{{ $blogId }}">
            @endif
            <input type="text" name="website" value="" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div class="mb-blog-email-input-wrap">
                <div class="mb-blog-email-input-inner">
                    <i class="las la-envelope mb-blog-email-input-icon"></i>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="mb-blog-email-input @error('email') is-invalid @enderror"
                        placeholder="{{ translate('Email address') }}"
                        required>
                </div>
                <button class="mb-blog-email-btn" type="submit">
                    <span>{{ $button }}</span>
                    <i class="las la-paper-plane"></i>
                </button>
            </div>
            @error('email')
                <div class="text-danger fs-12 mt-2">{{ $message }}</div>
            @enderror
            @if(session('blog_subscribe_error'))
                <div class="text-danger fs-12 mt-2">{{ session('blog_subscribe_error') }}</div>
            @endif
        </form>
    @endif
</div>
