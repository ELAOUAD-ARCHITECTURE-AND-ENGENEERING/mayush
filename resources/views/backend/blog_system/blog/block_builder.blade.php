@php
    $blog = $blog ?? null;
    $legacyDescription = old('description', $blog->description ?? '');
    $initialBlocks = old('content_blocks');

    if ($initialBlocks === null) {
        $initialBlocks = $blog && is_array($blog->content_blocks) ? $blog->content_blocks : [];
    } elseif (is_string($initialBlocks)) {
        $initialBlocks = json_decode($initialBlocks, true) ?: [];
    }

    $initialBlocksJson = json_encode($initialBlocks);
@endphp

<div class="blog-builder" data-blog-builder>
    <input type="hidden" name="content_blocks" data-blog-builder-input value="{{ $initialBlocksJson }}">
    <textarea name="description" class="d-none" data-blog-builder-description>{{ $legacyDescription }}</textarea>

    <div class="blog-builder__shell">
        <aside class="blog-builder__palette" aria-label="{{ translate('Article blocks') }}">
            <div class="blog-builder__palette-head">
                <span>{{ translate('Blocks') }}</span>
                <small>{{ translate('Drag to reorder') }}</small>
            </div>
            <button type="button" class="blog-builder__tool" data-add-block="heading">
                <i class="las la-heading" aria-hidden="true"></i>
                <span>{{ translate('Heading') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="paragraph">
                <i class="las la-align-left" aria-hidden="true"></i>
                <span>{{ translate('Paragraph') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="rich_text">
                <i class="las la-file-signature" aria-hidden="true"></i>
                <span>{{ translate('Rich Text') }}</span>
            </button>
            @can('manage_blog_html')
            <button type="button" class="blog-builder__tool" data-add-block="html">
                <i class="las la-code" aria-hidden="true"></i>
                <span>{{ translate('Advanced HTML') }}</span>
            </button>
            @endcan
            <button type="button" class="blog-builder__tool" data-add-block="image">
                <i class="las la-image" aria-hidden="true"></i>
                <span>{{ translate('Image') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="gallery">
                <i class="las la-images" aria-hidden="true"></i>
                <span>{{ translate('Gallery') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="quote">
                <i class="las la-quote-left" aria-hidden="true"></i>
                <span>{{ translate('Quote') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="list">
                <i class="las la-list-ul" aria-hidden="true"></i>
                <span>{{ translate('List') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="faq">
                <i class="las la-question-circle" aria-hidden="true"></i>
                <span>{{ translate('FAQ') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="product_recommendation">
                <i class="las la-box" aria-hidden="true"></i>
                <span>{{ translate('Products') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="shop_highlight">
                <i class="las la-store" aria-hidden="true"></i>
                <span>{{ translate('Shop') }}</span>
            </button>
            <button type="button" class="blog-builder__tool" data-add-block="divider">
                <i class="las la-minus" aria-hidden="true"></i>
                <span>{{ translate('Divider') }}</span>
            </button>
        </aside>

        <section class="blog-builder__canvas" aria-label="{{ translate('Article structure') }}">
            <div class="blog-builder__canvas-head">
                <div>
                    <h3>{{ translate('Article Structure') }}</h3>
                    <p>{{ translate('Build a professional article with sections, images, quotes and lists.') }}</p>
                </div>
                <div class="blog-builder__status" data-blog-builder-status aria-live="polite">
                    {{ translate('Draft ready') }}
                </div>
            </div>
            <div class="blog-builder__blocks" data-blog-builder-blocks></div>
            <div class="blog-builder__empty" data-blog-builder-empty>
                <i class="las la-layer-group" aria-hidden="true"></i>
                <strong>{{ translate('Start with a heading or paragraph') }}</strong>
                <span>{{ translate('Use the block tools to create a structured article before publishing.') }}</span>
            </div>
        </section>
    </div>
</div>
