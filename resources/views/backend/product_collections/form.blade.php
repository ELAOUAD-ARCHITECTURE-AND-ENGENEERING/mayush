@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3">{{ $collection->exists ? translate('Edit Product Collection') : translate('Create Product Collection') }}</h1>
            </div>
            <div class="col text-right">
                <a href="{{ route('product-collections.index') }}" class="btn btn-soft-secondary">{{ translate('Back to Collections') }}</a>
            </div>
        </div>
    </div>

    <form action="{{ $collection->exists ? route('product-collections.update', $collection) : route('product-collections.store') }}" method="POST">
        @csrf
        @if ($collection->exists)
            @method('PUT')
        @endif

        <div class="row gutters-16">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0 h6">{{ translate('Collection Identity') }}</h5></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ translate('Name') }}</label>
                            <input type="text" class="form-control" name="name" required value="{{ old('name', $collection->name) }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Slug') }}</label>
                            <input type="text" class="form-control" name="slug" value="{{ old('slug', $collection->slug) }}" placeholder="{{ translate('Generated automatically when empty') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Short Description') }}</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $collection->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Header Image') }}</label>
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div></div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="hero_image" class="selected-files" value="{{ old('hero_image', $collection->hero_image) }}">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="mb-0 h6">{{ translate('Product Rules') }}</h5></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>{{ translate('Collection Mode') }}</label>
                            <select class="form-control aiz-selectpicker" name="mode">
                                @foreach (['hybrid' => 'Hybrid', 'manual' => 'Manual', 'dynamic' => 'Dynamic'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('mode', $collection->mode ?: 'hybrid') === $value)>{{ translate($label) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">{{ translate('Hybrid shows manually pinned products together with products matched by rules.') }}</small>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Categories') }}</label>
                            <select class="form-control aiz-selectpicker" name="category_ids[]" multiple data-live-search="true">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(in_array($category->id, old('category_ids', $collection->category_ids ?: [])))>{{ $category->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Brands') }}</label>
                            <select class="form-control aiz-selectpicker" name="brand_ids[]" multiple data-live-search="true">
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(in_array($brand->id, old('brand_ids', $collection->brand_ids ?: [])))>{{ $brand->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Sellers') }}</label>
                            <select class="form-control aiz-selectpicker" name="seller_ids[]" multiple data-live-search="true">
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}" @selected(in_array($seller->id, old('seller_ids', $collection->seller_ids ?: [])))>{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Tags') }}</label>
                            <input type="text" class="form-control" name="tags" value="{{ old('tags', $collection->tags) }}" placeholder="{{ translate('Comma-separated tags') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group"><label>{{ translate('Minimum Price') }}</label><input type="number" min="0" step="0.01" class="form-control" name="min_price" value="{{ old('min_price', $collection->min_price) }}"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group"><label>{{ translate('Maximum Price') }}</label><input type="number" min="0" step="0.01" class="form-control" name="max_price" value="{{ old('max_price', $collection->max_price) }}"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ translate('Pinned Products') }}</label>
                            <select class="form-control aiz-selectpicker" name="product_ids[]" multiple data-live-search="true">
                                @php $selectedProductIds = old('product_ids', $collection->products->pluck('id')->all()); @endphp
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProductIds))>{{ $product->getTranslation('name') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0 h6">{{ translate('Publishing') }}</h5></div>
                    <div class="card-body">
                        <label class="aiz-switch aiz-switch-success d-block mb-3">
                            <input type="checkbox" name="status" value="1" @checked(old('status', $collection->exists ? $collection->status : true))>
                            <span></span> <span class="ml-2">{{ translate('Published') }}</span>
                        </label>
                        <label class="aiz-switch aiz-switch-success d-block mb-3">
                            <input type="checkbox" name="show_best_selling" value="1" @checked(old('show_best_selling', $collection->exists ? $collection->show_best_selling : true))>
                            <span></span> <span class="ml-2">{{ translate('Show Most Buying Products') }}</span>
                        </label>
                        <label class="aiz-switch aiz-switch-success d-block mb-3">
                            <input type="checkbox" name="show_recently_viewed" value="1" @checked(old('show_recently_viewed', $collection->exists ? $collection->show_recently_viewed : true))>
                            <span></span> <span class="ml-2">{{ translate('Show Recently Viewed Products') }}</span>
                        </label>
                        <div class="form-group">
                            <label>{{ translate('Default Sort') }}</label>
                            <select class="form-control aiz-selectpicker" name="default_sort">
                                @foreach (['newest' => 'Newest', 'popular' => 'Popularity', 'price-asc' => 'Price low to high', 'price-desc' => 'Price high to low', 'oldest' => 'Oldest'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('default_sort', $collection->default_sort ?: 'newest') === $value)>{{ translate($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>{{ translate('Start Date') }}</label><input type="datetime-local" class="form-control" name="starts_at" value="{{ old('starts_at', optional($collection->starts_at)->format('Y-m-d\\TH:i')) }}"></div>
                        <div class="form-group"><label>{{ translate('End Date') }}</label><input type="datetime-local" class="form-control" name="ends_at" value="{{ old('ends_at', optional($collection->ends_at)->format('Y-m-d\\TH:i')) }}"></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5 class="mb-0 h6">{{ translate('SEO') }}</h5></div>
                    <div class="card-body">
                        <div class="form-group"><label>{{ translate('Meta Title') }}</label><input type="text" class="form-control" name="meta_title" value="{{ old('meta_title', $collection->meta_title) }}"></div>
                        <div class="form-group"><label>{{ translate('Meta Description') }}</label><textarea class="form-control" name="meta_description" rows="3">{{ old('meta_description', $collection->meta_description) }}</textarea></div>
                        <div class="form-group">
                            <label>{{ translate('Share Image') }}</label>
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend"><div class="input-group-text bg-soft-secondary">{{ translate('Browse') }}</div></div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="meta_image" class="selected-files" value="{{ old('meta_image', $collection->meta_image) }}">
                            </div>
                            <div class="file-preview box sm"></div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-success btn-block">{{ translate('Save Collection') }}</button>
            </div>
        </div>
    </form>
@endsection
