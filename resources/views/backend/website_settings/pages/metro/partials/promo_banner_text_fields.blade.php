@php
    $bannerTitle = $bannerTitle ?? '';
    $bannerDescription = $bannerDescription ?? '';
    $bannerCta = $bannerCta ?? '';
    $bannerCollectionId = $bannerCollectionId ?? '';
    $productCollections = \App\Models\ProductCollection::orderBy('name')->get();
@endphp
<div class="row gutters-10 mt-3 banner-text-fields">
    <div class="col-lg-6">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Banner Title') }}</label>
            <textarea class="form-control" name="{{ $bannerKey }}_titles[]" rows="3" placeholder="{{ translate('Headline shown over this banner') }}">{{ trim(strip_tags(app(\App\Services\BannerTextSanitizerService::class)->sanitize($bannerTitle))) }}</textarea>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Description') }}</label>
            <textarea class="form-control" name="{{ $bannerKey }}_descriptions[]" rows="3" placeholder="{{ translate('Supporting text shown below the title') }}">{{ trim(strip_tags(app(\App\Services\BannerTextSanitizerService::class)->sanitize($bannerDescription))) }}</textarea>
        </div>
    </div>
</div>
<div class="row gutters-10 align-items-end">
    <div class="col-lg-4">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Button Text') }}</label>
            <input type="text" class="form-control" name="{{ $bannerKey }}_cta_texts[]" value="{{ $bannerCta }}" placeholder="{{ translate('Shop Now') }}">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Product Collection') }}</label>
            <select class="form-control aiz-selectpicker" name="{{ $bannerKey }}_collection_ids[]" data-live-search="true">
                <option value="">{{ translate('Use custom URL') }}</option>
                @foreach ($productCollections as $productCollection)
                    <option value="{{ $productCollection->id }}" @selected((string) $bannerCollectionId === (string) $productCollection->id)>{{ $productCollection->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group text-lg-right">
            <button type="button" class="btn btn-soft-primary btn-sm js-banner-preview">
                <i class="las la-eye mr-1"></i>{{ translate('Preview Banner') }}
            </button>
        </div>
    </div>
</div>
