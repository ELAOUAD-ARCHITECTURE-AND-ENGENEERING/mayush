@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Product Bulk Upload')}}</h5>
        </div>
        <div class="card-body">
            <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                <strong>{{ translate('Step 1')}}:</strong>
                <p>1. {{translate('Download the skeleton file and fill it with proper data')}}.</p>
                <p>2. {{translate('You can download the example file to understand how the data must be filled')}}.</p>
                <p>3. {{translate('Once you have downloaded and filled the skeleton file, upload it in the form below and submit')}}.</p>
                <p>4. {{translate('After uploading products you need to edit them and set product\'s images and choices')}}.</p>
            </div>
            <br>
            <div class="">
                <a href="{{ static_asset('download/product_bulk_demo.xlsx') }}" download><button class="btn btn-info">{{ translate('Download CSV')}}</button></a>
            </div>
            <div class="alert" style="color: #004085;background-color: #cce5ff;border-color: #b8daff;margin-bottom:0;margin-top:10px;">
                <strong>{{translate('Step 2')}}:</strong>
                <p>1. {{translate('Category and Brand should be in numerical id')}}.</p>
                <p>2. {{translate('You can download the pdf to get Category and Brand id')}}.</p>
                <p>3. {{translate('The uploaded file must use valid existing category and brand IDs, numeric price and stock values, unique SKU values, and valid image URLs.')}}</p>
                <p>4. {{translate('Brand ID is optional. Leave it empty if the product has no brand.')}}</p>
            </div>
            <br>
            <div class="">
                <a href="{{ route('pdf.download_category') }}"><button class="btn btn-info">{{translate('Download Category')}}</button></a>
                <a href="{{ route('pdf.download_brand') }}"><button class="btn btn-info">{{translate('Download Brand')}}</button></a>
            </div>
            <br>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6"><strong>{{translate('Upload Product File')}}</strong></h5>
        </div>
        <div class="card-body">
            <form class="form-horizontal" action="{{ route('bulk_product_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group row">
                    <div class="col-sm-9">
                        <div class="custom-file">
    						<label class="custom-file-label">
    							<input type="file" name="bulk_file" class="custom-file-input" accept=".xlsx,.xls,.csv" required>
    							<span class="custom-file-name">{{ translate('Choose File')}}</span>
    						</label>
    					</div>
                        @error('bulk_file')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-info">{{translate('Upload CSV')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection
