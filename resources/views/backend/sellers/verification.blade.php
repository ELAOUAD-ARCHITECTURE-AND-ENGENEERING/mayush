@extends('backend.layouts.app')

@section('content')

<div class="card">
  <div class="card-header">
      <h5 class="mb-0 h6">{{ translate('Seller Verification') }}</h5>
      @if ($shop->verification_status != 1 && $shop->verification_info != null)
        <div class="pull-right clearfix">
            <form action="{{ route('sellers.reject', $shop->id) }}" method="POST" class="d-inline-block">
                @csrf
                <input type="hidden" name="rejection_reason" value="{{ translate('Rejected from verification review.') }}">
                <button type="submit" class="btn btn-circle btn-danger d-innline-block">{{translate('Reject')}}</button>
            </form>
            <form action="{{ route('sellers.approve', $shop->id) }}" method="POST" class="d-inline-block">
                @csrf
                <button type="submit" class="btn btn-circle btn-success d-innline-block">{{translate('Accept')}}</button>
            </form>
        </div>
      @endif
  </div>
  <div class="card-body row">
      <div class="col-md-5">
          <h6 class="mb-4">{{ translate('User Info') }}</h6>
          <p class="text-muted">
              <strong>{{ translate('Name') }} :</strong>
              <span class="ml-2">{{ $shop->user->name }}</span>
          </p>
          <p class="text-muted">
              <strong>{{translate('Email')}}</strong>
              <span class="ml-2">{{ $shop->user->email }}</span>
          </p>
          <p class="text-muted">
              <strong>{{translate('Address')}}</strong>
              <span class="ml-2">{{ $shop->user->address }}</span>
          </p>
          <p class="text-muted">
              <strong>{{translate('Phone')}}</strong>
              <span class="ml-2">{{ $shop->user->phone }}</span>
          </p>
          <br>

          <h6 class="mb-4">{{ translate('Shop Info') }}</h6>
          <p class="text-muted">
              <strong>{{translate('Shop Name')}}</strong>
              <span class="ml-2">{{ $shop->user->shop->name }}</span>
          </p>
          <p class="text-muted">
              <strong>{{translate('Address')}}</strong>
              <span class="ml-2">{{ $shop->address }}</span>
          </p>
      </div>
      <div class="col-md-5">
        <h6 class="mb-4">{{ translate('Verification Info') }}</h6>
        @if ($shop->verification_info != null)
          <table class="table table-striped table-bordered" cellspacing="0" width="100%">
              <tbody>
                  @foreach (json_decode($shop->verification_info) as $key => $info)
                      <tr>
                          <th class="text-muted">{{ $info->label }}</th>
                          @if ($info->type == 'text' || $info->type == 'select' || $info->type == 'radio')
                              <td>{{ $info->value }}</td>
                          @elseif ($info->type == 'multi_select')
                              <td>
                                  {{ implode(', ', json_decode($info->value)) }}
                              </td>
                          @elseif ($info->type == 'file')
                              <td>
                                  <a href="{{ my_asset($info->value) }}" target="_blank" class="btn-info">{{translate('Click here')}}</a>
                              </td>
                          @endif
                      </tr>
                  @endforeach
              </tbody>
          </table>
        @endif
        @if ($shop->verification_status != 1 && $shop->verification_info != null)
          <div class="text-center">
              <form action="{{ route('sellers.reject', $shop->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <input type="hidden" name="rejection_reason" value="{{ translate('Rejected from verification review.') }}">
                  <button type="submit" class="btn btn-sm btn-danger d-innline-block">{{translate('Reject')}}</button>
              </form>
              <form action="{{ route('sellers.approve', $shop->id) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success d-innline-block">{{translate('Accept')}}</button>
              </form>
          </div>
        @endif
      </div>
  </div>
</div>

@endsection
