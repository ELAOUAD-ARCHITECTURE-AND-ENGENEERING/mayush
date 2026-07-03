@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8 mx-auto">
                <div class="card-body p-2rem">
                    <h6 class="text-center">{{ translate('Select Header Layouts') }}</h6>
                    <form action="{{ (Route::has('settings.select-header') ? route('settings.select-header') : '#') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mx-1 header-card">
                            @foreach ($element_types as $key => $element_type)
                                @php
                                    $header_number = preg_replace('/[^0-9]/', '', $element_type->name);
                                    $header_preview = 'assets/img/headers/header' . $header_number . '.webp';
                                    $has_header_preview = $header_number && file_exists(public_path($header_preview));
                                @endphp
                                
                                    <div class="card text-center px-2 py-3 w-100" data-header="{{$element_type->name}}">
                                        <input type="radio" hidden 
                                               id="element_type_{{ $element_type->id }}" 
                                               name="header_element"
                                               value="{{ $element_type->id }}" 
                                               @if(get_setting('header_element') == $element_type->id) checked @endif>

                                        @if ($has_header_preview)
                                            <img src="{{ static_asset($header_preview) }}"
                                                class="card-img-top mx-auto" alt="header layout">
                                        @else
                                            <div class="header-layout-preview header-layout-preview-marketplace mx-auto">
                                                <div class="preview-top-row">
                                                    <span class="preview-logo">Mayush</span>
                                                    <span class="preview-location"></span>
                                                    <span class="preview-search"></span>
                                                    <span class="preview-cta"></span>
                                                </div>
                                                <div class="preview-bottom-row">
                                                    <span></span><span></span><span></span><span></span>
                                                </div>
                                            </div>
                                        @endif

                                        <p class="mt-2 mb-0 font-weight-bold">
                                            {{ $element_type->name }}
                                        </p>
                                    </div>
                                
                            @endforeach
                        </div>

                        <div class="row p-1">
                            <div class="col-md-8 d-none d-md-block">
                                <button type="button" class="btn bg-blue-color2 text-primary w-100 ">
                                    <small class="font-weight-bold">
                                        {{ translate('You have selected') }} <span id="dynamic-text">...</span>
                                    </small>
                                </button>
                            </div>
                            <div class="col-md-4 d-flex align-items-center justify-content-end">
                                <button type="submit"
                                    class="btn btn-success  w-100 btn-md rounded-2 fs-14 fw-700 shadow-success">
                                    {{ translate('Save') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
    </div>
</div>
@endsection

@section('script')
<style>
    .header-layout-preview {
        width: 100%;
        max-width: 420px;
        height: 94px;
        border-radius: 4px;
        overflow: hidden;
        background: #111827;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
    }
    .header-layout-preview .preview-top-row {
        height: 58px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
    }
    .header-layout-preview .preview-logo {
        color: #fff;
        font-weight: 800;
        font-size: 15px;
        line-height: 1;
    }
    .header-layout-preview .preview-location {
        width: 42px;
        height: 20px;
        border-radius: 3px;
        background: rgba(255,255,255,0.22);
    }
    .header-layout-preview .preview-search {
        flex: 1;
        height: 28px;
        border-radius: 3px;
        background: #fff;
        position: relative;
    }
    .header-layout-preview .preview-search::after {
        content: "";
        position: absolute;
        right: 0;
        top: 0;
        width: 36px;
        height: 100%;
        background: #d97434;
    }
    .header-layout-preview .preview-cta {
        width: 40px;
        height: 24px;
        border-radius: 3px;
        background: rgba(255,255,255,0.22);
    }
    .header-layout-preview .preview-bottom-row {
        height: 36px;
        background: #243244;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 10px;
    }
    .header-layout-preview .preview-bottom-row span {
        width: 48px;
        height: 8px;
        border-radius: 999px;
        background: rgba(255,255,255,0.72);
    }
</style>
<script>
    // make whole card clickable
    $('.header-card .card').click(function(e) {
        if (!$(e.target).is('input[type=radio]')) {
            $(this).find('input[type=radio]').prop('checked', true).trigger('change');
        }
    });

    // when radio changes → update border + dynamic text
    $('input[name="header_element"]').change(function() {
        $('.header-card .card').removeClass('border border-primary border-2');
        $(this).closest('.card').addClass('border border-primary border-2');
        $('#dynamic-text').text($(this).closest('.card').data('header'));
    });

    // initialize selected card on page load
    var selected = $('input[name="header_element"]:checked');
    if (selected.length) {
        selected.closest('.card').addClass('border border-primary border-2');
        $('#dynamic-text').text(selected.closest('.card').data('header'));
    }

    // pointer cursor
    $(document).ready(function() {
        $('.header-card .card').css('cursor', 'pointer');
    });
</script>

@endsection
