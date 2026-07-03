@extends('backend.layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ static_asset('assets/blog/css/blog-builder.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 h6">{{ translate('New Article') }}</h5>
                <a href="{{ route('author.blogs.index') }}" class="btn btn-soft-dark btn-sm">
                    <i class="las la-arrow-left mr-1"></i>{{ translate('My Articles') }}
                </a>
            </div>
            <div class="card-body">
                @include('backend.blog_system.author.form', ['blog' => null])
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ static_asset('assets/blog/js/blog-builder.js') }}"></script>
<script>
    $('#title').on('input', function () {
        if (!$('#slug').val()) {
            $('#slug').val(this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
        }
    });
</script>
@endsection
