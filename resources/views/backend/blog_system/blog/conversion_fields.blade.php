@php
    $selectedProductIds = isset($blog) && $blog->relationLoaded('products')
        ? $blog->products->pluck('id')->all()
        : [];
@endphp

<div class="border-top pt-4 mt-4">
    <h5 class="mb-3 h6">{{ translate('Blog Conversion') }}</h5>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Hero Image') }}</label>
        <div class="col-md-9">
            <div class="input-group" data-toggle="aizuploader" data-type="image">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="hero_image" class="selected-files" value="{{ $blog->hero_image ?? '' }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Featured Article') }}</label>
        <div class="col-md-9">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input type="checkbox" name="is_featured" value="1" @checked(!empty($blog) && $blog->is_featured)>
                <span></span>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Article Badge') }}</label>
        <div class="col-md-9">
            <select class="form-control aiz-selectpicker" name="badge_type">
                <option value="">{{ translate('None') }}</option>
                @foreach(['buying_guide', 'expert_pick', 'inspiration', 'comparison', 'custom'] as $badge)
                    <option value="{{ $badge }}" @selected(($blog->badge_type ?? '') === $badge)>{{ ucfirst(str_replace('_', ' ', $badge)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Custom Badge Text') }}</label>
        <div class="col-md-9">
            <input type="text" class="form-control" name="custom_badge_text" value="{{ $blog->custom_badge_text ?? '' }}">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Canonical URL') }}</label>
        <div class="col-md-9">
            <input type="url" class="form-control" name="canonical_url" value="{{ $blog->canonical_url ?? '' }}">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Schema Enabled') }}</label>
        <div class="col-md-9">
            <label class="aiz-switch aiz-switch-success mb-0">
                <input type="checkbox" name="schema_enabled" value="1" @checked(!isset($blog) || $blog->schema_enabled)>
                <span></span>
            </label>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Vendor Spotlight') }}</label>
        <div class="col-md-9">
            <select class="form-control aiz-selectpicker" name="shop_id" data-live-search="true">
                <option value="">{{ translate('No vendor spotlight') }}</option>
                @foreach(($shops ?? collect()) as $shop)
                    <option value="{{ $shop->id }}" @selected(($blog->shop_id ?? null) == $shop->id)>{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Vendor Quote') }}</label>
        <div class="col-md-9">
            <textarea name="vendor_quote" rows="3" class="form-control">{{ $blog->vendor_quote ?? '' }}</textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Manual Products') }}</label>
        <div class="col-md-9">
            <select class="form-control aiz-selectpicker" name="product_ids[]" multiple data-live-search="true" data-selected-text-format="count">
                @foreach(($assignable_products ?? collect()) as $product)
                    @php
                        $productLabel = $product->getTranslation('name') . ' - ' . $product->slug;
                        $productImage = uploaded_asset($product->thumbnail_img);
                        $productPrice = strip_tags((string) home_discounted_base_price($product));
                    @endphp
                    <option
                        value="{{ $product->id }}"
                        data-content="<div class='d-flex align-items-center'><img src='{{ $productImage }}' class='size-40px img-fit mr-2' alt=''><div><div class='fw-600'>{{ e($product->getTranslation('name')) }}</div><small class='text-muted'>{{ e($productPrice) }} · {{ e($product->slug) }}</small></div></div>"
                        @selected(in_array($product->id, $selectedProductIds))>
                        {{ $product->getTranslation('name') }} - {{ $product->slug }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted d-block mt-2">{{ translate('Only approved and published products are available for assignment. Use the search box to find products by name or slug; selected order is saved as product priority.') }}</small>
        </div>
    </div>
</div>
