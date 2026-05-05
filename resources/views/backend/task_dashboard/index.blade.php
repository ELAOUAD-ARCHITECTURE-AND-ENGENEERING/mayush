@extends('backend.layouts.app')

@section('content')
<div class="row gutters-16">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header border-bottom-0 pb-0">
                <h4 class="mb-0">{{ translate('Unified Admin Task Dashboard') }}</h4>
                <p class="text-muted mt-2">{{ translate('A high-level overview of manual tasks and areas that require admin attention.') }}</p>
            </div>
        </div>
    </div>

    <!-- Pending Refunds -->
    <div class="col-lg-4 col-sm-6 mb-4">
        <div class="dashboard-box bg-soft-warning h-100 p-4 border border-warning rounded">
            <div class="text-center">
                <i class="las la-money-bill-wave fa-3x text-warning mb-3"></i>
                <h3 class="fs-30 fw-600 text-warning mb-1">{{ $pendingRefunds }}</h3>
                <p class="fs-14 text-dark mb-0 fw-600">{{ translate('Pending Refunds') }}</p>
            </div>
        </div>
    </div>

    <!-- Unverified Sellers -->
    <div class="col-lg-4 col-sm-6 mb-4">
        <div class="dashboard-box bg-soft-danger h-100 p-4 border border-danger rounded">
            <div class="text-center">
                <i class="las la-store fa-3x text-danger mb-3"></i>
                <h3 class="fs-30 fw-600 text-danger mb-1">{{ $unverifiedSellers }}</h3>
                <p class="fs-14 text-dark mb-0 fw-600">{{ translate('Unverified Sellers') }}</p>
                @if($unverifiedSellers > 0)
                    <a href="{{ route('sellers.registration_pending') }}" class="btn btn-danger btn-sm mt-3">{{ translate('Review Sellers') }}</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Failed Payments -->
    <div class="col-lg-4 col-sm-6 mb-4">
        <div class="dashboard-box bg-soft-secondary h-100 p-4 border border-secondary rounded">
            <div class="text-center">
                <i class="las la-credit-card fa-3x text-secondary mb-3"></i>
                <h3 class="fs-30 fw-600 text-secondary mb-1">{{ $failedPayments }}</h3>
                <p class="fs-14 text-dark mb-0 fw-600">{{ translate('Unpaid/Failed Payments') }}</p>
                @if($failedPayments > 0)
                    <a href="{{ route('unpaid_orders.index') }}" class="btn btn-secondary btn-sm mt-3">{{ translate('View Orders') }}</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stalled Orders -->
    <div class="col-lg-6 col-sm-6 mb-4">
        <div class="dashboard-box bg-soft-info h-100 p-4 border border-info rounded">
            <div class="text-center">
                <i class="las la-shipping-fast fa-3x text-info mb-3"></i>
                <h3 class="fs-30 fw-600 text-info mb-1">{{ $stalledOrders }}</h3>
                <p class="fs-14 text-dark mb-0 fw-600">{{ translate('Stalled Orders (Shipped > 5 days)') }}</p>
                @if($stalledOrders > 0)
                    <a href="{{ route('all_orders.index') }}" class="btn btn-info btn-sm mt-3">{{ translate('Investigate Orders') }}</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Expiring Subscriptions -->
    <div class="col-lg-6 col-sm-6 mb-4">
        <div class="dashboard-box bg-soft-success h-100 p-4 border border-success rounded">
            <div class="text-center">
                <i class="las la-calendar-check fa-3x text-success mb-3"></i>
                <h3 class="fs-30 fw-600 text-success mb-1">{{ $expiringSubscriptions }}</h3>
                <p class="fs-14 text-dark mb-0 fw-600">{{ translate('Subscriptions Expiring Soon (< 7 Days)') }}</p>
            </div>
        </div>
    </div>

    <!-- Blog Editorial Commerce -->
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-header border-bottom-0 pb-0">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                    <div>
                        <h5 class="mb-1">{{ translate('Blog Editorial Commerce') }}</h5>
                        <p class="text-muted mb-0">{{ translate('Manage articles, product embeds, email capture, SEO schema, and subscriber leads from one place.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0">
                        @can('add_blog')
                            <a href="{{ route('blog.create') }}" class="btn btn-primary btn-sm mr-2">
                                <i class="las la-plus mr-1"></i>{{ translate('Add Article') }}
                            </a>
                        @endcan
                        @can('view_blogs')
                            <a href="{{ route('blog.conversion-settings') }}" class="btn btn-soft-primary btn-sm">
                                <i class="las la-sliders-h mr-1"></i>{{ translate('Setup Blog') }}
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row gutters-16">
                    <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                        <a href="{{ route('blog.index') }}" class="d-block border rounded p-3 h-100 text-reset hov-shadow-md">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-600">{{ translate('Published Articles') }}</span>
                                <i class="las la-newspaper fs-24 text-primary"></i>
                            </div>
                            <div class="fs-28 fw-700 mt-2">{{ $publishedBlogs }}</div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                        <a href="{{ route('blog.index') }}" class="d-block border rounded p-3 h-100 text-reset hov-shadow-md">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-600">{{ translate('Draft Articles') }}</span>
                                <i class="las la-edit fs-24 text-warning"></i>
                            </div>
                            <div class="fs-28 fw-700 mt-2">{{ $draftBlogs }}</div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6 mb-3 mb-lg-0">
                        <a href="{{ route('blog.conversion-settings') }}" class="d-block border rounded p-3 h-100 text-reset hov-shadow-md">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-600">{{ translate('Featured Articles') }}</span>
                                <i class="las la-star fs-24 text-success"></i>
                            </div>
                            <div class="fs-28 fw-700 mt-2">{{ $featuredBlogs }}</div>
                        </a>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <a href="{{ route('blog.conversion-subscribers') }}" class="d-block border rounded p-3 h-100 text-reset hov-shadow-md">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-600">{{ translate('Blog Subscribers') }}</span>
                                <i class="las la-envelope-open-text fs-24 text-info"></i>
                            </div>
                            <div class="fs-28 fw-700 mt-2">{{ $blogSubscribers }}</div>
                        </a>
                    </div>
                </div>
                <div class="mt-3 d-flex flex-wrap">
                    <a href="{{ route('blog') }}" target="_blank" class="btn btn-soft-dark btn-sm mr-2 mb-2">
                        <i class="las la-external-link-alt mr-1"></i>{{ translate('Preview Public Blog') }}
                    </a>
                    <a href="{{ route('blog-category.index') }}" class="btn btn-soft-secondary btn-sm mr-2 mb-2">
                        <i class="las la-folder-open mr-1"></i>{{ translate('Manage Categories') }}
                    </a>
                    <a href="{{ route('blog.conversion-subscribers.export') }}" class="btn btn-soft-success btn-sm mb-2">
                        <i class="las la-file-csv mr-1"></i>{{ translate('Export Subscribers') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
