@extends('frontend.layouts.app')

@section('content')
<section class="gry-bg py-4 profile">
    <div class="container">
        <div class="row cols-xs-space cols-sm-space cols-md-space">
            <div class="col-lg-3 d-none d-lg-block">
                @include('frontend.inc.user_side_nav')
            </div>

            <div class="col-lg-9">
                <div class="main-content">
                    <!-- Page title -->
                    <div class="page-title">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-12">
                                <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                    {{ translate('Affiliate Payment History') }}
                                </h2>
                            </div>
                        </div>
                    </div>

                    @if (count($affiliate_logs) > 0)
                        <div class="card no-border mt-4">
                            <table class="table table-sm table-hover table-responsive-md">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ translate('Date') }}</th>
                                        <th>{{ translate('Amount') }}</th>
                                        <th>{{ translate('Order ID') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($affiliate_logs as $key => $log)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ date('d-m-Y', strtotime($log->created_at)) }}</td>
                                            <td>{{ single_price($log->amount) }}</td>
                                            <td>{{ $log->order_id }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="aiz-pagination">
                            {{ $affiliate_logs->links() }}
                        </div>
                    @else
                        <div class="col-12">
                            <div class="text-center pt-5">
                                <i class="las la-frown la-3x mb-3"></i>
                                <h5>{{ translate('No history found.') }}</h5>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
