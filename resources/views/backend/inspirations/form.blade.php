@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ $inspiration->exists ? translate('Edit Inspiration') : translate('Add Inspiration') }}</h1>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('inspirations.index') }}" class="btn btn-soft-secondary">
                <i class="las la-arrow-left"></i> {{ translate('Back') }}
            </a>
            @if($inspiration->exists && $inspiration->hero_image)
                <a href="{{ route('inspirations.mapper', $inspiration) }}" class="btn btn-primary">
                    <i class="las la-map-marker-alt"></i> {{ translate('Hotspot Mapper') }}
                </a>
            @endif
        </div>
    </div>
</div>

<form action="{{ $inspiration->exists ? route('inspirations.update', $inspiration) : route('inspirations.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($inspiration->exists)
        @method('PUT')
    @endif

    <div class="row gutters-8">
        {{-- Main content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Content') }} ({{ translate('French') }})</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Title') }} (FR) <span class="text-danger">*</span></label>
                        <input type="text" name="title_fr" value="{{ old('title_fr', $inspiration->title_fr) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Subtitle') }} (FR)</label>
                        <input type="text" name="subtitle_fr" value="{{ old('subtitle_fr', $inspiration->subtitle_fr) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} (FR)</label>
                        <textarea name="description_fr" class="form-control" rows="4">{{ old('description_fr', $inspiration->description_fr) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Content') }} ({{ translate('Arabic') }})</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Title') }} (AR)</label>
                        <input type="text" name="title_ar" value="{{ old('title_ar', $inspiration->title_ar) }}" class="form-control" dir="rtl">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Subtitle') }} (AR)</label>
                        <input type="text" name="subtitle_ar" value="{{ old('subtitle_ar', $inspiration->subtitle_ar) }}" class="form-control" dir="rtl">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Description') }} (AR)</label>
                        <textarea name="description_ar" class="form-control" rows="4" dir="rtl">{{ old('description_ar', $inspiration->description_ar) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Scene Image') }}</h5></div>
                <div class="card-body">
                    @if($inspiration->hero_image)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" class="img-fluid rounded" style="max-height: 300px;">
                            <p class="text-muted small mt-1">
                                {{ $inspiration->hero_image_width ?? '?' }}x{{ $inspiration->hero_image_height ?? '?' }}px
                            </p>
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ $inspiration->hero_image ? translate('Replace Image') : translate('Upload Image') }}</label>
                        <input type="file" name="hero_image" class="form-control-file" accept="image/*">
                        <small class="text-muted">{{ translate('Recommended: high-resolution professional interior photography (min 1200px wide)') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Settings') }}</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-control aiz-selectpicker">
                            <option value="draft" {{ old('status', $inspiration->status ?? 'draft') == 'draft' ? 'selected' : '' }}>{{ translate('Draft') }}</option>
                            <option value="published" {{ old('status', $inspiration->status) == 'published' ? 'selected' : '' }}>{{ translate('Published') }}</option>
                            <option value="archived" {{ old('status', $inspiration->status) == 'archived' ? 'selected' : '' }}>{{ translate('Archived') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Slug') }}</label>
                        <input type="text" name="slug" value="{{ old('slug', $inspiration->slug) }}" class="form-control" placeholder="{{ translate('Auto-generated from title') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Sort Order') }}</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $inspiration->sort_order ?? 0) }}" class="form-control" min="0">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $inspiration->is_featured) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_featured">{{ translate('Featured') }}</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="show_on_home" name="show_on_home" value="1" {{ old('show_on_home', $inspiration->show_on_home) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="show_on_home">{{ translate('Show on Home') }}</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0 h6">{{ translate('Scheduling') }}</h5></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>{{ translate('Starts At') }}</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($inspiration->starts_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ translate('Ends At') }}</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($inspiration->ends_at)->format('Y-m-d\TH:i')) }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">
                    {{ $inspiration->exists ? translate('Update') : translate('Create') }}
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
