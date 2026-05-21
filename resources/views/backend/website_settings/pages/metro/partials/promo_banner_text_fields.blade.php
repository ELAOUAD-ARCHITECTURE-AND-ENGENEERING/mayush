@php
    $bannerTitle = $bannerTitle ?? '';
    $bannerDescription = $bannerDescription ?? '';
    $bannerCta = $bannerCta ?? '';
@endphp
<div class="row gutters-10 mt-3 banner-text-fields">
    <div class="col-lg-6">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Banner Title') }}</label>
            <textarea class="aiz-banner-text-editor form-control" name="{{ $bannerKey }}_titles[]" data-min-height="105" placeholder="{{ translate('Headline shown over this banner') }}">{{ $bannerTitle }}</textarea>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('Description') }}</label>
            <textarea class="aiz-banner-text-editor form-control" name="{{ $bannerKey }}_descriptions[]" data-min-height="105" placeholder="{{ translate('Supporting text shown below the title') }}">{{ $bannerDescription }}</textarea>
        </div>
    </div>
</div>
<div class="row gutters-10 align-items-end">
    <div class="col-lg-8">
        <div class="form-group">
            <label class="fs-12 fw-600 text-uppercase text-muted">{{ translate('CTA Button Text') }}</label>
            <input type="text" class="form-control" name="{{ $bannerKey }}_cta_texts[]" value="{{ $bannerCta }}" placeholder="{{ translate('Shop Now') }}">
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
