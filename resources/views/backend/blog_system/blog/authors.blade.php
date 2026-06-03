@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('Blog Authors') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('blog.index') }}" class="btn btn-soft-dark">
                <i class="las la-arrow-left mr-1"></i>{{ translate('Back to Posts') }}
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Assign Author Role') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('blog.authors.assign') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>{{ translate('User') }}</label>
                        <select class="form-control aiz-selectpicker" name="user_id" data-live-search="true" required>
                            <option value="">{{ translate('Select user') }}</option>
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="las la-user-plus mr-1"></i>{{ translate('Make Author') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <form method="GET">
                <div class="card-header row gutters-5">
                    <div class="col">
                        <h5 class="mb-0 h6">{{ translate('Current Authors') }}</h5>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="{{ translate('Type & Enter') }}">
                    </div>
                </div>
            </form>
            <div class="card-body">
                <table class="table aiz-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ translate('Author') }}</th>
                            <th>{{ translate('Articles') }}</th>
                            <th class="text-right">{{ translate('Options') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($authors as $author)
                            <tr>
                                <td>
                                    <div class="fw-700">{{ $author->name }}</div>
                                    <div class="text-muted fs-12">{{ $author->email }}</div>
                                </td>
                                <td>{{ $author->blogs_count }}</td>
                                <td class="text-right">
                                    <a href="{{ route('blog.index', ['author_id' => $author->id]) }}" class="btn btn-soft-info btn-icon btn-circle btn-sm" title="{{ translate('View articles') }}">
                                        <i class="las la-newspaper"></i>
                                    </a>
                                    <form action="{{ route('blog.authors.remove', $author->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Remove author role') }}">
                                            <i class="las la-user-minus"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="aiz-pagination">
                    {{ $authors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
