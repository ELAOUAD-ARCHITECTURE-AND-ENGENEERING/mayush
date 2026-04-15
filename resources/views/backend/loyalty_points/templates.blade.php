@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{translate('Point Templates')}}</h1>
            <p class="text-muted">{{translate('Create reusable rules for allocating points automatically. Eg: "5% of selling price".')}}</p>
        </div>
        <div class="col-md-6 text-md-right">
            <a href="{{ route('admin.loyalty.points.dashboard') }}" class="btn btn-outline-secondary">
                <i class="las la-angle-left"></i> {{translate('Back to Dashboard')}}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- List -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Available Templates')}}</h5>
            </div>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{translate('Name')}}</th>
                            <th>{{translate('Rule')}}</th>
                            <th>{{translate('Min Cap')}}</th>
                            <th>{{translate('Max Cap')}}</th>
                            <th class="text-right">{{translate('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->name }}</td>
                            <td>
                                @if($template->type == 'fixed')
                                    <span class="badge badge-inline badge-info">Fixed: {{ $template->value }} pts</span>
                                @else
                                    <span class="badge badge-inline badge-primary">Dynamic: {{ $template->value }}% of Price</span>
                                @endif
                            </td>
                            <td>{{ $template->min_threshold ?? 'None' }}</td>
                            <td>{{ $template->max_threshold ?? 'None' }}</td>
                            <td class="text-right">
                                <a href="javascript:void(0);" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('admin.loyalty.points.templates.destroy', $template->id)}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Add New Template')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.loyalty.points.templates.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>{{translate('Template Name')}}</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Standard Electronics Rate" required>
                    </div>
                    <div class="form-group">
                        <label>{{translate('Calculation Type')}}</label>
                        <select name="type" class="form-control aiz-selectpicker" id="template_type" required>
                            <option value="percentage_of_price">{{translate('Dynamic: Percentage of Unit Price')}}</option>
                            <option value="fixed">{{translate('Static: Fixed Amount')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label id="val_label">{{translate('Value (Percentage %)')}}</label>
                        <input type="number" step="0.01" name="value" class="form-control" placeholder="10" required>
                    </div>
                    
                    <div class="dynamic-caps">
                        <hr>
                        <p class="text-muted small">{{translate('Optional: Set guardrails so cheap items do not give too few points, or expensive items do not give too many.')}}</p>
                        <div class="form-group">
                            <label>{{translate('Minimum Point Output')}}</label>
                            <input type="number" name="min_threshold" class="form-control" placeholder="e.g. 5">
                        </div>
                        <div class="form-group">
                            <label>{{translate('Maximum Point Output')}}</label>
                            <input type="number" name="max_threshold" class="form-control" placeholder="e.g. 5000">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">{{translate('Save Template')}}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
<script type="text/javascript">
    $('#template_type').on('change', function() {
        if($(this).val() == 'fixed') {
            $('#val_label').text('{{translate("Value (Fixed Points)")}}');
            $('.dynamic-caps').hide();
        } else {
            $('#val_label').text('{{translate("Value (Percentage %)")}}');
            $('.dynamic-caps').show();
        }
    });
</script>
@endsection
