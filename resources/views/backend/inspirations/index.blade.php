@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3">{{ translate('Inspirations') }}</h1>
        </div>
        @can('add_inspiration')
        <div class="col-md-6 text-md-right">
            <a href="{{ route('inspirations.create') }}" class="btn btn-primary">
                <span>{{ translate('Add New Inspiration') }}</span>
            </a>
        </div>
        @endcan
    </div>
</div>

<div class="card">
    <form id="sort_inspirations" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ translate('All Inspirations') }}</h5>
            </div>
            <div class="col-md-3">
                <select class="form-control form-control-sm aiz-selectpicker" name="status" onchange="this.form.submit()">
                    <option value="">{{ translate('Filter by Status') }}</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ translate('Draft') }}</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ translate('Published') }}</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ translate('Archived') }}</option>
                </select>
            </div>
        </div>
    </form>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ translate('Image') }}</th>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Status') }}</th>
                    <th>{{ translate('Products') }}</th>
                    <th>{{ translate('Featured') }}</th>
                    <th>{{ translate('Sort Order') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspirations as $key => $inspiration)
                <tr>
                    <td>{{ $inspirations->firstItem() + $key }}</td>
                    <td>
                        @if($inspiration->hero_image)
                            <img src="{{ asset('storage/' . $inspiration->hero_image) }}" alt="" class="size-60px img-fit rounded">
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->title_fr }}</td>
                    <td>
                        @if($inspiration->status === 'published')
                            <span class="badge badge-inline badge-success">{{ translate('Published') }}</span>
                        @elseif($inspiration->status === 'draft')
                            <span class="badge badge-inline badge-secondary">{{ translate('Draft') }}</span>
                        @else
                            <span class="badge badge-inline badge-warning">{{ translate('Archived') }}</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->items_count ?? 0 }}</td>
                    <td>
                        @can('edit_inspiration')
                            <form method="POST" action="{{ route('inspirations.featured', $inspiration) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_featured" value="{{ $inspiration->is_featured ? 0 : 1 }}">
                                <button type="submit" class="btn btn-sm {{ $inspiration->is_featured ? 'btn-info' : 'btn-soft-secondary' }}" aria-label="{{ translate('Toggle featured') }}">
                                    {{ $inspiration->is_featured ? translate('Featured') : translate('Not featured') }}
                                </button>
                            </form>
                        @else
                            <span class="badge badge-inline {{ $inspiration->is_featured ? 'badge-info' : 'badge-secondary' }}">
                                {{ $inspiration->is_featured ? translate('Featured') : translate('Not featured') }}
                            </span>
                        @endcan
                        @if($inspiration->show_on_home)
                            <span class="badge badge-inline badge-primary">{{ translate('Home') }}</span>
                        @endif
                    </td>
                    <td>{{ $inspiration->sort_order }}</td>
                    <td class="text-right">
                        @can('edit_inspiration')
                            @if($inspiration->hero_image)
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('inspirations.mapper', $inspiration) }}" title="{{ translate('Mapper') }}">
                                <i class="las la-map-marker-alt"></i>
                            </a>
                            @endif
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('inspirations.edit', $inspiration) }}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                        @endcan
                        @can('delete_inspiration')
                            <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{ route('inspirations.destroy', $inspiration) }}" title="{{ translate('Delete') }}">
                                <i class="las la-trash"></i>
                            </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
                @if($inspirations->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">{{ translate('No inspirations found') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $inspirations->appends(request()->input())->links() }}
        </div>
    </div>
</div>
@endsection
