@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3">{{ translate('My Articles') }}</h1>
        </div>
        <div class="col text-right">
            <a href="{{ route('author.blogs.create') }}" class="btn btn-primary">
                <i class="las la-plus mr-1"></i>{{ translate('New Article') }}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ translate('Title') }}</th>
                    <th>{{ translate('Category') }}</th>
                    <th>{{ translate('Workflow') }}</th>
                    <th>{{ translate('Updated') }}</th>
                    <th class="text-right">{{ translate('Options') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($blogs as $blog)
                    <tr>
                        <td>
                            <div class="fw-700">{{ $blog->title }}</div>
                            @if($blog->review_note)
                                <div class="text-warning fs-12">{{ $blog->review_note }}</div>
                            @endif
                        </td>
                        <td>{{ optional($blog->category)->category_name ?? '--' }}</td>
                        <td>
                            <span class="badge badge-soft-dark">{{ ucfirst(str_replace('_', ' ', $blog->workflow_status ?: 'draft')) }}</span>
                        </td>
                        <td>{{ optional($blog->updated_at)->diffForHumans() }}</td>
                        <td class="text-right">
                            <a class="btn btn-soft-info btn-icon btn-circle btn-sm" href="{{ route('blog.preview', $blog->id) }}" target="_blank" title="{{ translate('Preview') }}">
                                <i class="las la-eye"></i>
                            </a>
                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('author.blogs.edit', $blog->id) }}" title="{{ translate('Edit') }}">
                                <i class="las la-pen"></i>
                            </a>
                            @if($blog->workflow_status !== 'published')
                                <form action="{{ route('author.blogs.destroy', $blog->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="{{ translate('Delete') }}">
                                        <i class="las la-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $blogs->links() }}
        </div>
    </div>
</div>
@endsection
