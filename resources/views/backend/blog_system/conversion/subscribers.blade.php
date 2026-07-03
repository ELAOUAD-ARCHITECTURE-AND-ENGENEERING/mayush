@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Blog Subscriber Logs') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('blog.conversion-settings') }}" class="btn btn-soft-primary">
                {{ translate('Settings') }}
            </a>
            <a href="{{ route('blog.conversion-subscribers.export', request()->query()) }}" class="btn btn-primary">
                {{ translate('Export CSV') }}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <form method="GET">
        <div class="card-header row gutters-5">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="email" value="{{ request('email') }}" placeholder="{{ translate('Search email') }}">
            </div>
            <div class="col-md-3">
                <select class="form-control form-control-sm aiz-selectpicker" name="placement">
                    <option value="">{{ translate('All placements') }}</option>
                    @foreach(['listing_inline', 'mid_article', 'sidebar', 'post_read'] as $placement)
                        <option value="{{ $placement }}" @selected(request('placement') === $placement)>{{ ucfirst(str_replace('_', ' ', $placement)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-control form-control-sm aiz-selectpicker" name="provider">
                    <option value="">{{ translate('All providers') }}</option>
                    @foreach(['local', 'mailchimp', 'klaviyo', 'webhook'] as $provider)
                        <option value="{{ $provider }}" @selected(request('provider') === $provider)>{{ ucfirst($provider) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 text-right">
                <button class="btn btn-sm btn-primary" type="submit">{{ translate('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Email') }}</th>
                    <th data-breakpoints="lg">{{ translate('Article') }}</th>
                    <th data-breakpoints="lg">{{ translate('Placement') }}</th>
                    <th data-breakpoints="lg">{{ translate('Provider') }}</th>
                    <th data-breakpoints="lg">{{ translate('Status') }}</th>
                    <th data-breakpoints="lg">{{ translate('Date') }}</th>
                    <th data-breakpoints="lg">{{ translate('IP') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $key => $log)
                    <tr>
                        <td>{{ ($key + 1) + ($logs->currentPage() - 1) * $logs->perPage() }}</td>
                        <td>{{ $log->email }}</td>
                        <td>{{ $log->blog_title ?: '-' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $log->placement)) }}</td>
                        <td>{{ ucfirst($log->provider) }}</td>
                        <td>{{ $log->provider_status ?: '-' }}</td>
                        <td>{{ optional($log->subscribed_at ?: $log->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->ip_address ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ translate('No subscriber logs found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="aiz-pagination mt-3">
            {{ $logs->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection
