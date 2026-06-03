@extends('backend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ static_asset('assets/blog/css/blog-builder.css') }}">
@endsection

@section('content')

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Blog Information')}}</h5>
            </div>
            <div class="card-body">
                <form id="add_form" class="form-horizontal" action="{{ route('blog.store') }}" method="POST">
                    @csrf
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Blog Title')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Blog Title')}}" onkeyup="makeSlug(this.value)" id="title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row" id="category">
                        <label class="col-md-3 col-from-label">
                            {{translate('Category')}} 
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" required>
                                <option>--</option>
                                @foreach ($blog_categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Slug')}}
                            <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" placeholder="{{translate('Slug')}}" name="slug" id="slug" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{translate('Banner')}} 
                            <small>(1300x650)</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ translate('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="banner" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Short Description')}}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <textarea name="short_description" rows="5" class="form-control" required=""></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">
                            {{translate('Description')}}
                        </label>
                        <div class="col-md-9">
                            @include('backend.blog_system.blog.block_builder', ['blog' => null])
                        </div>
                    </div>

                    @can('manage_blog_authors')
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ translate('Author') }}</label>
                            <div class="col-md-9">
                                <select class="form-control aiz-selectpicker" name="user_id" data-live-search="true">
                                    <option value="">{{ translate('Current admin') }}</option>
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}">{{ $author->name }} ({{ $author->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endcan

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{ translate('Publish At') }}</label>
                        <div class="col-md-9">
                            <input type="datetime-local" class="form-control" name="published_at">
                            <small class="form-text text-muted">{{ translate('Leave empty to publish immediately when approved.') }}</small>
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Title')}}</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title')}}">
                        </div>
                    </div>
                    
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="signinSrEmail">
                            {{translate('Meta Image')}} 
                            <small>(200x200)+</small>
                        </label>
                        <div class="col-md-9">
                            <div class="input-group" data-toggle="aizuploader" data-type="image">
                                <div class="input-group-prepend">
                                    <div class="input-group-text bg-soft-secondary font-weight-medium">
                                        {{ translate('Browse')}}
                                    </div>
                                </div>
                                <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                <input type="hidden" name="meta_img" class="selected-files">
                            </div>
                            <div class="file-preview box sm">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">{{translate('Meta Description')}}</label>
                        <div class="col-md-9">
                            <textarea name="meta_description" rows="5" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 col-form-label">
                            {{translate('Meta Keywords')}}
                        </label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" placeholder="{{translate('Meta Keywords')}}">
                        </div>
                    </div>

                    @include('backend.blog_system.blog.conversion_fields', [
                        'blog' => null,
                        'assignable_products' => $assignable_products ?? collect(),
                        'shops' => $shops ?? collect(),
                    ])
                    
                    <div class="form-group mb-0 text-right">
                        <button type="submit" name="workflow_action" value="draft" class="btn btn-blog-draft mr-2">
                            <i class="las la-save mr-1"></i>{{ translate('Save Draft') }}
                        </button>
                        <button type="submit" name="workflow_action" value="submit" class="btn btn-soft-primary mr-2">
                            <i class="las la-paper-plane mr-1"></i>{{ translate('Submit for Review') }}
                        </button>
                        @can('publish_blog')
                            <button type="submit" name="workflow_action" value="publish" class="btn btn-primary">
                                <i class="las la-check-circle mr-1"></i>{{ translate('Publish') }}
                            </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
     function makeSlug(val) {
        if (!val) return;

        $.ajax({
            url: '{{ route("generate.slug") }}',
            method: 'POST',
            data: {
                title: val,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                $('#slug').val(response.slug);
            }
        });
    }
</script>
<script src="{{ static_asset('assets/blog/js/blog-builder.js') }}"></script>
@endsection
