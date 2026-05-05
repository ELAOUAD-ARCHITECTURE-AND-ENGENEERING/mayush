@extends('backend.layouts.app')

@section('content')
@php($blogSettingsService = app(\App\Services\Blog\BlogSettingsService::class))
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Blog Conversion Settings') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('blog.conversion-subscribers') }}" class="btn btn-soft-primary">
                {{ translate('Subscriber Logs') }}
            </a>
        </div>
    </div>
</div>

<form action="{{ route('blog.conversion-settings.update') }}" method="POST">
    @csrf
    <div class="row gutters-16">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Product Embeds') }}</h5>
                </div>
                <div class="card-body">
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_enable_product_embeds', 'label' => translate('Enable product embeds')])
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_enable_product_schema', 'label' => translate('Enable product schema')])
                    <div class="form-group">
                        <label>{{ translate('Products per embed') }}</label>
                        <input type="number" min="1" max="12" class="form-control" name="blog_products_per_embed" value="{{ get_setting('blog_products_per_embed', 4) }}">
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ translate('Product cache duration') }}</label>
                        <input type="number" min="1" max="1440" class="form-control" name="blog_product_embed_cache_minutes" value="{{ get_setting('blog_product_embed_cache_minutes', 15) }}">
                        <small class="text-muted">{{ translate('Minutes') }}</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Article Experience') }}</h5>
                </div>
                <div class="card-body">
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_enable_table_of_contents', 'label' => translate('Enable table of contents')])
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_enable_share_bar', 'label' => translate('Enable share bar')])
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">{{ translate('Email Capture') }}</h5>
                </div>
                <div class="card-body">
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_email_enable_listing_inline', 'label' => translate('Listing inline capture')])
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_email_enable_sidebar', 'label' => translate('Sidebar capture')])
                    @include('backend.blog_system.conversion.toggle', ['name' => 'blog_email_enable_post_read', 'label' => translate('Post-read capture')])
                    <div class="form-group">
                        <label>{{ translate('Email provider') }}</label>
                        <select class="form-control aiz-selectpicker" name="blog_email_provider">
                            @foreach(['local' => 'Local only', 'mailchimp' => 'Mailchimp', 'klaviyo' => 'Klaviyo', 'webhook' => 'Custom webhook'] as $value => $label)
                                <option value="{{ $value }}" @selected(get_setting('blog_email_provider', 'local') === $value)>{{ translate($label) }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">{{ translate('Provider failures are safely logged and visitors only see the configured success or error message.') }}</small>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fs-13 fw-600 mb-3">{{ translate('Mailchimp') }}</h6>
                        <div class="form-group">
                            <label>{{ translate('Mailchimp API key') }}</label>
                            <input type="password" class="form-control" name="blog_mailchimp_api_key" autocomplete="new-password" placeholder="{{ $blogSettingsService->secretIsConfigured('blog_mailchimp_api_key') ? translate('Configured - leave blank to keep current key') : translate('Enter API key') }}">
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ translate('Mailchimp list ID') }}</label>
                            <input type="text" class="form-control" name="blog_mailchimp_list_id" value="{{ get_setting('blog_mailchimp_list_id') }}">
                        </div>
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <h6 class="fs-13 fw-600 mb-3">{{ translate('Klaviyo') }}</h6>
                        <div class="form-group">
                            <label>{{ translate('Klaviyo private API key') }}</label>
                            <input type="password" class="form-control" name="blog_klaviyo_api_key" autocomplete="new-password" placeholder="{{ $blogSettingsService->secretIsConfigured('blog_klaviyo_api_key') ? translate('Configured - leave blank to keep current key') : translate('Enter API key') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Klaviyo list ID') }}</label>
                            <input type="text" class="form-control" name="blog_klaviyo_list_id" value="{{ get_setting('blog_klaviyo_list_id') }}">
                        </div>
                        <div class="form-group mb-0">
                            <label>{{ translate('Klaviyo API revision') }}</label>
                            <input type="text" class="form-control" name="blog_klaviyo_revision" value="{{ get_setting('blog_klaviyo_revision', '2026-04-15') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Custom webhook URL') }}</label>
                        <input type="url" class="form-control" name="blog_webhook_url" value="{{ get_setting('blog_webhook_url') }}">
                    </div>
                    <div class="form-group mb-0">
                        <label>{{ translate('Success message') }}</label>
                        <input type="text" class="form-control" name="blog_email_success_message" value="{{ get_setting('blog_email_success_message', translate("You're in! Check your inbox.")) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        <button type="submit" class="btn btn-primary">{{ translate('Save Settings') }}</button>
    </div>
</form>
@endsection
