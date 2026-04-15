@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Loyalty Points Management')}}</h1>
            <p class="text-muted">{{translate('Bulk assign loyalty points to products, categories, or via CSV import.')}}</p>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.loyalty.points.templates') }}" class="btn btn-outline-info">
                <span>{{translate('Point Templates')}}</span>
            </a>
            <a href="{{ route('admin.loyalty.points.history') }}" class="btn btn-outline-primary">
                <span>{{translate('Audit Log & Rollbacks')}}</span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- CSV Import/Export -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('CSV Operations')}}</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">{{translate('Download current product points, edit the points in Excel, and upload to bulk apply.')}}</p>
                <div class="d-flex mb-3">
                    <a href="{{ route('admin.loyalty.points.export') }}" class="btn btn-primary btn-block">
                        <i class="las la-download"></i> {{translate('Export CSV')}}
                    </a>
                </div>
                <form action="{{ route('admin.loyalty.points.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <div class="custom-file">
                            <label class="custom-file-label">
                                <input type="file" name="csv_file" class="custom-file-input" accept=".csv" required>
                                <span class="custom-file-name">{{ translate('Choose CSV File') }}</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="las la-upload"></i> {{translate('Import CSV & Apply Points')}}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Bulk Assignment -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Categorical Bulk Assignment')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.loyalty.points.bulk') }}" method="POST">
                    @csrf
                    <input type="hidden" name="assignment_type" value="category">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{translate('Select Parent Category')}}</label>
                                <select class="form-control aiz-selectpicker" name="category_assign_id" data-live-search="true" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->getTranslation('name') }}</option>
                                        @foreach ($category->childrenCategories as $childCategory)
                                            @include('backend.product.categories.child_category', ['child_category' => $childCategory])
                                        @endforeach
                                    @endforeach
                                </select>
                                <small class="text-muted">{{translate('Points apply cascade-down to all selected subcategories')}}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{translate('Assign Value Or Template')}}</label>
                                <select class="form-control aiz-selectpicker" name="point_value" required>
                                    <optgroup label="Fixed Points">
                                        <option value="0">{{translate('0 Points (Remove)')}}</option>
                                        <option value="10">10 Points</option>
                                        <option value="50">50 Points</option>
                                        <option value="100">100 Points</option>
                                        <option value="500">500 Points</option>
                                    </optgroup>
                                    <optgroup label="Templates">
                                        @foreach($templates as $template)
                                            <option value="template_{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('{{translate('Are you sure you want to alter all products in this category hierarchy? Valid Old points will be backed up.')}}')">
                            {{translate('Apply to Category')}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_products" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('Product Target Assignment') }}</h5>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search" @isset($search) value="{{ $search }}" @endisset placeholder="{{ translate('Type product name') }}">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="number" class="form-control form-control-sm" name="min_price" placeholder="{{ translate('Min Price') }}" value="{{ request('min_price') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100" type="submit">{{ translate('Filter') }}</button>
            </div>
        </div>
    </form>
    
    <div class="card-body">
        <form action="{{ route('admin.loyalty.points.bulk') }}" method="POST" id="bulk_product_assign">
            @csrf
            <input type="hidden" name="assignment_type" value="selected">
            <input type="hidden" name="product_ids" id="product_ids">
            
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th data-breakpoints="lg">
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-all">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </th>
                        <th>{{translate('Item')}}</th>
                        <th>{{translate('Price')}}</th>
                        <th>{{translate('Current Earn Points')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="form-group">
                                <div class="aiz-checkbox-inline">
                                    <label class="aiz-checkbox">
                                        <input type="checkbox" class="check-one" value="{{$product->id}}">
                                        <span class="aiz-square-check"></span>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="row gutters-5 w-200px w-md-300px mw-100">
                                <div class="col-auto">
                                    <img src="{{ uploaded_asset($product->thumbnail_img)}}" alt="Image" class="size-50px img-fit">
                                </div>
                                <div class="col">
                                    <span class="text-muted text-truncate-2">{{ $product->getTranslation('name') }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ single_price($product->unit_price) }}</td>
                        <td>
                            <span class="badge badge-inline badge-success">{{ $product->earn_point }} pts</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex align-items-center bg-light p-3 mt-3 align-items-center">
                <div class="mr-3">
                    <label class="mb-0">{{translate('Assign Points to Selected:')}}</label>
                </div>
                <div class="mr-3">
                    <select class="form-control aiz-selectpicker" name="point_value" required>
                        <optgroup label="Fixed Points">
                            <option value="0">{{translate('0 Points (Remove)')}}</option>
                            <option value="10">10 Points</option>
                            <option value="50">50 Points</option>
                            <option value="100">100 Points</option>
                            <option value="500">500 Points</option>
                        </optgroup>
                        <optgroup label="Templates">
                            @foreach($templates as $template)
                                <option value="template_{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" onclick="bulkAssignSubmit()">{{translate('Apply Points')}}</button>
                </div>
            </div>

            <div class="aiz-pagination mt-4">
                {{ $products->appends(request()->input())->links() }}
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script type="text/javascript">
    $(document).on("change", ".check-all", function() {
        if(this.checked) {
            $('.check-one:checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('.check-one:checkbox').each(function() {
                this.checked = false;
            });
        }
    });
    
    function bulkAssignSubmit(){
        var selectedIds = [];
        $('.check-one:checked').each(function(){
            selectedIds.push($(this).val());
        });
        
        if(selectedIds.length > 0){
            $('#product_ids').val(selectedIds.join(','));
            $('#bulk_product_assign').submit();
        } else {
            AIZ.plugins.notify('danger', '{{ translate('Please select products first') }}');
        }
    }
</script>
@endsection
