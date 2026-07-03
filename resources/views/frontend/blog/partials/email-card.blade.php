@php
    $placement = $placement ?? 'post_read';
    $headline = $headline ?? translate('Get Moroccan design ideas in your inbox');
    $text = $text ?? translate('Join Mayush readers for practical decor guides, buying tips, and marketplace picks.');
    $button = $button ?? translate('Subscribe');
    $blogId = isset($blog) && $blog ? $blog->id : null;
@endphp

<div class="mb-blog-email-card border bg-soft-primary p-4 my-4">
    <h2 class="fs-18 fw-700 mb-2">{{ $headline }}</h2>
    <p class="fs-14 opacity-70 mb-3">{{ $text }}</p>

    @if(session('blog_subscribe_success'))
        <div class="alert alert-success mb-0">{{ session('blog_subscribe_success') }}</div>
    @else
        <form method="POST" action="{{ route('blog.subscribe') }}" class="mb-blog-subscribe-form">
            @csrf
            <input type="hidden" name="placement" value="{{ $placement }}">
            @if($blogId)
                <input type="hidden" name="blog_id" value="{{ $blogId }}">
            @endif
            <input type="text" name="website" value="" class="d-none" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div class="input-group">
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control rounded-0 @error('email') is-invalid @enderror"
                    placeholder="{{ translate('Email address') }}"
                    required>
                <div class="input-group-append">
                    <button class="btn btn-primary rounded-0 fw-700" type="submit">{{ $button }}</button>
                </div>
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
