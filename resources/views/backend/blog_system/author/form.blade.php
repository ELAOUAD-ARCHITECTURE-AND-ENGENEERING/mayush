@php
    $blog = $blog ?? null;
    $isEdit = (bool) $blog;
@endphp

<form class="form-horizontal" action="{{ $isEdit ? route('author.blogs.update', $blog->id) : route('author.blogs.store') }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PATCH')
    @endif

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Article Title') }} <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <input type="text" class="form-control" name="title" id="title" value="{{ old('title', $blog->title ?? '') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Category') }} <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <select class="form-control aiz-selectpicker" name="category_id" data-live-search="true" required data-selected="{{ old('category_id', $blog->category_id ?? '') }}">
                <option value="">{{ translate('Select category') }}</option>
                @foreach($blog_categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Slug') }} <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <input type="text" class="form-control" name="slug" id="slug" value="{{ old('slug', $blog->slug ?? '') }}" required>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Banner') }} <small>(1300x650)</small></label>
        <div class="col-md-9">
            <div class="input-group" data-toggle="aizuploader" data-type="image">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                </div>
                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                <input type="hidden" name="banner" class="selected-files" value="{{ old('banner', $blog->banner ?? '') }}">
            </div>
            <div class="file-preview box sm"></div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Short Description') }} <span class="text-danger">*</span></label>
        <div class="col-md-9">
            <textarea name="short_description" rows="4" class="form-control" required>{{ old('short_description', $blog->short_description ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Article Builder') }}</label>
        <div class="col-md-9">
            @include('backend.blog_system.blog.block_builder', ['blog' => $blog])
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Meta Title') }}</label>
        <div class="col-md-9">
            <input type="text" class="form-control" name="meta_title" value="{{ old('meta_title', $blog->meta_title ?? '') }}">
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Meta Description') }}</label>
        <div class="col-md-9">
            <textarea name="meta_description" rows="4" class="form-control">{{ old('meta_description', $blog->meta_description ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-md-3 col-form-label">{{ translate('Meta Keywords') }}</label>
        <div class="col-md-9">
            <input type="text" class="form-control" name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords ?? '') }}">
        </div>
    </div>

    <div class="form-group mb-0 text-right">
        @if($isEdit)
            <a href="{{ route('blog.preview', $blog->id) }}" target="_blank" class="btn btn-soft-info mr-2">
                <i class="las la-eye mr-1"></i>{{ translate('Preview') }}
            </a>
        @endif
        <button type="submit" name="workflow_action" value="draft" class="btn btn-blog-draft mr-2">
            <i class="las la-save mr-1"></i>{{ translate('Save Draft') }}
        </button>
        <button type="submit" name="workflow_action" value="submit" class="btn btn-primary">
            <i class="las la-paper-plane mr-1"></i>{{ translate('Submit for Review') }}
        </button>
    </div>
</form>
