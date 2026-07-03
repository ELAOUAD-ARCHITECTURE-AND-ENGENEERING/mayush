@extends('backend.layouts.app')

@section('content')
    <div class="aiz-titlebar text-left mt-2 mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3">{{ translate('Product Collections') }}</h1>
            </div>
            <div class="col text-right">
                <a href="{{ route('product-collections.create') }}" class="btn btn-primary">
                    <i class="las la-plus mr-1"></i>{{ translate('Add Collection') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Mode') }}</th>
                        <th>{{ translate('Status') }}</th>
                        <th>{{ translate('CTA URL') }}</th>
                        <th class="text-right">{{ translate('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($collections as $collection)
                        <tr>
                            <td>{{ $collection->name }}</td>
                            <td class="text-capitalize">{{ $collection->mode }}</td>
                            <td>
                                <span class="badge badge-inline badge-{{ $collection->status ? 'success' : 'secondary' }}">
                                    {{ $collection->status ? translate('Published') : translate('Draft') }}
                                </span>
                            </td>
                            <td><a href="{{ route('product-collections.show', $collection->slug) }}" target="_blank">{{ route('product-collections.show', $collection->slug) }}</a></td>
                            <td class="text-right">
                                <a href="{{ route('product-collections.edit', $collection) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm">
                                    <i class="las la-edit"></i>
                                </a>
                                <form action="{{ route('product-collections.destroy', $collection) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-soft-danger btn-icon btn-circle btn-sm" onclick="return confirm('{{ translate('Delete this collection?') }}')">
                                        <i class="las la-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ translate('No product collections created yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="aiz-pagination mt-3">{{ $collections->links() }}</div>
        </div>
    </div>
@endsection
