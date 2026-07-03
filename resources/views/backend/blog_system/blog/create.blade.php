@extends('backend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ static_asset('assets/blog/css/blog-builder.css') }}">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .editorial-workspace {
            background: #f8f9fa;
        }
        .sticky-top-bar {
            position: sticky;
            top: 60px; /* Adjust based on your header height */
            z-index: 1020;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e5ec;
            padding: 15px 0;
            margin-bottom: 25px;
        }
        .editor-container {
            background: #fff;
            border: 1px solid #e2e5ec;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .sidebar-section {
            background: #fff;
            border: 1px solid #e2e5ec;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        .metrics-bar {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            gap: 15px;
        }
    </style>
@endsection

@section('content')
<form id="add_form" action="{{ route('blog.store') }}" method="POST">
    @csrf
    
    <!-- Sticky Top Bar -->
    <div class="sticky-top-bar">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 h6 mr-3">{{ translate('Create New Article') }}</h5>
                <div class="metrics-bar d-none d-md-flex">
                    <span id="word-count"><i class="las la-pen-nib"></i> 0 {{ translate('words') }}</span>
                    <span id="reading-time"><i class="las la-clock"></i> 0 {{ translate('min read') }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="workflow_action" value="draft" class="btn btn-outline-secondary mr-2">
                    <i class="las la-save mr-1"></i>{{ translate('Save Draft') }}
                </button>
                <button type="submit" name="workflow_action" value="preview" class="btn btn-outline-info mr-2">
                    <i class="las la-eye mr-1"></i>{{ translate('Save & Preview') }}
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
        </div>
    </div>

    <div class="row">
        <!-- Main Editor Column -->
        <div class="col-lg-8">
            <div class="editor-container mb-4">
                <div class="form-group mb-4">
                    <input type="text" placeholder="{{translate('Article Title...')}}" onkeyup="makeSlug(this.value)" id="title" name="title" class="form-control form-control-lg border-0 px-0" style="font-size: 2rem; font-weight: 700; background: transparent; box-shadow: none;" required>
                </div>
                
                <div class="form-group mb-4">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-0 bg-light text-muted">{{ url('/blog/') }}/</span>
                        </div>
                        <input type="text" placeholder="{{translate('slug-will-be-here')}}" name="slug" id="slug" class="form-control border-0 bg-light" required>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold">{{translate('Short Description')}} <span class="text-danger">*</span></label>
                    <textarea name="short_description" rows="3" class="form-control" placeholder="{{translate('A brief summary of the article...')}}" required=""></textarea>
                </div>

                <div class="form-group mb-4">
                    <label class="font-weight-bold">{{translate('Article Content')}}</label>
                    @include('backend.blog_system.blog.block_builder', ['blog' => null])
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            
            <div class="sidebar-section">
                <h6 class="font-weight-bold mb-3">{{ translate('Publishing Details') }}</h6>
                
                <div class="form-group">
                    <label>{{translate('Category')}} <span class="text-danger">*</span></label>
                    <select class="form-control aiz-selectpicker" name="category_id" id="category_id" data-live-search="true" required>
                        <option value="">{{ translate('Select Category') }}</option>
                        @foreach ($blog_categories as $category)
                            <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ translate('Publish At') }}</label>
                    <input type="datetime-local" class="form-control" name="published_at">
                    <small class="form-text text-muted">{{ translate('Leave empty to publish immediately when approved.') }}</small>
                </div>

                @can('manage_blog_authors')
                    <div class="form-group">
                        <label>{{ translate('Author') }}</label>
                        <select class="form-control aiz-selectpicker" name="user_id" data-live-search="true">
                            <option value="">{{ translate('Current admin') }}</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }} ({{ $author->email }})</option>
                            @endforeach
                        </select>
                    </div>
                @endcan
            </div>

            <div class="sidebar-section">
                <h6 class="font-weight-bold mb-3">{{ translate('Media') }}</h6>
                
                <div class="form-group">
                    <label>
                        {{translate('Banner Image')}} 
                        <small>(1300x650)</small>
                    </label>
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="banner" class="selected-files">
                    </div>
                    <div class="file-preview box sm"></div>
                </div>
            </div>

            <div class="sidebar-section">
                <h6 class="font-weight-bold mb-3">{{ translate('SEO Optimization') }}</h6>
                
                <div class="form-group">
                    <label>{{translate('Meta Title')}}</label>
                    <input type="text" class="form-control" name="meta_title" placeholder="{{translate('Meta Title')}}">
                </div>

                <div class="form-group">
                    <label>{{translate('Meta Description')}}</label>
                    <textarea name="meta_description" rows="3" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>{{translate('Meta Keywords')}}</label>
                    <input type="text" class="form-control" name="meta_keywords" placeholder="{{translate('keyword1, keyword2')}}">
                </div>

                <div class="form-group">
                    <label>
                        {{translate('Meta Image')}} 
                        <small>(200x200)+</small>
                    </label>
                    <div class="input-group" data-toggle="aizuploader" data-type="image">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-soft-secondary">{{ translate('Browse')}}</div>
                        </div>
                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                        <input type="hidden" name="meta_img" class="selected-files">
                    </div>
                    <div class="file-preview box sm"></div>
                </div>
            </div>

            <div class="sidebar-section">
                <h6 class="font-weight-bold mb-3">{{ translate('Conversion & Products') }}</h6>
                @include('backend.blog_system.blog.conversion_fields', [
                    'blog' => null,
                    'assignable_products' => $assignable_products ?? collect(),
                    'shops' => $shops ?? collect(),
                ])
            </div>

        </div>
    </div>
</form>
@endsection

@section('script')
<script>
    // Permission injection for the builder
    window.mayushBlogConfig = {
        canManageHtml: {{ Auth::user()->can('manage_blog_html') ? 'true' : 'false' }}
    };

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

    // Word count calculation
    setInterval(function() {
        let text = '';
        $('.editor-container textarea, .editor-container .ql-editor').each(function() {
            if ($(this).hasClass('ql-editor')) {
                text += ' ' + $(this).text();
            } else {
                text += ' ' + $(this).val();
            }
        });
        
        let words = text.trim().split(/\s+/).filter(word => word.length > 0).length;
        let readingTime = Math.ceil(words / 200); // Average 200 words per minute
        
        $('#word-count').html('<i class="las la-pen-nib"></i> ' + words + ' {{ translate("words") }}');
        $('#reading-time').html('<i class="las la-clock"></i> ' + readingTime + ' {{ translate("min read") }}');
    }, 2000);
</script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="{{ static_asset('assets/blog/js/blog-builder.js') }}"></script>
@endsection
