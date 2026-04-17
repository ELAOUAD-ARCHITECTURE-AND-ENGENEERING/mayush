@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="align-items-center">
			<h1 class="h3">{{translate('Affiliate Users')}}</h1>
	</div>
</div>


<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Affiliate Applications')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{translate('Name')}}</th>
                    <th>{{translate('Email')}}</th>
                    <th>{{translate('Status')}}</th>
                    <th>{{translate('Balance')}}</th>
                    <th class="text-right">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $key => $affiliate_user)
                    @if($affiliate_user->user != null)
                        <tr>
                            <td>{{ ($key+1) + ($users->currentPage() - 1)*$users->perPage() }}</td>
                            <td>{{$affiliate_user->user->name}}</td>
                            <td>{{$affiliate_user->user->email}}</td>
                            <td>
                                @if($affiliate_user->status == 1)
                                    <span class="badge badge-inline badge-success">{{translate('Approved')}}</span>
                                @else
                                    <span class="badge badge-inline badge-info">{{translate('Pending')}}</span>
                                @endif
                            </td>
                            <td>{{single_price($affiliate_user->balance)}}</td>
                            <td class="text-right">
                                @if($affiliate_user->status == 0)
                                    <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('affiliate.users.approve', $affiliate_user->id)}}" title="{{ translate('Approve') }}">
                                        <i class="las la-check-circle"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $users->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
