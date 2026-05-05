@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{translate('All Posts')}}</h1>
        </div>
        @can('add_blog')
            <div class="col text-right">
                @can('view_blogs')
                    <a href="{{ route('blog.conversion-settings') }}" class="btn btn-soft-primary mr-2">
                        <i class="las la-sliders-h mr-1"></i>
                        <span>{{ translate('Conversion Setup') }}</span>
                    </a>
                    <a href="{{ route('blog.conversion-subscribers') }}" class="btn btn-soft-success mr-2">
                        <i class="las la-envelope-open-text mr-1"></i>
                        <span>{{ translate('Subscriber Logs') }}</span>
                    </a>
                @endcan
                <a href="{{ route('blog') }}" target="_blank" class="btn btn-soft-dark mr-2">
                    <i class="las la-external-link-alt mr-1"></i>
                    <span>{{ translate('Preview Blog') }}</span>
                </a>
                <a href="{{ route('blog.create') }}" class="btn btn-circle btn-info">
                    <span>{{translate('Add New Post')}}</span>
                </a>
            </div>
        @endcan
    </div>
</div>
<br>

<div class="card mb-4 border-primary">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center">
                    <div class="size-48px rounded bg-soft-primary text-primary d-flex align-items-center justify-content-center mr-3">
                        <i class="las la-bullhorn fs-28"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ translate('Editorial-Commerce Blog Tools') }}</h5>
                        <p class="mb-0 text-muted">{{ translate('Use conversion settings to control hero articles, product embeds, email capture, schema, sidebar products, vendor spotlight, and subscriber exports.') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <a href="{{ route('blog.conversion-settings') }}" class="btn btn-primary btn-sm mr-2">{{ translate('Setup Blog') }}</a>
                <a href="{{ route('blog.create') }}" class="btn btn-soft-primary btn-sm">{{ translate('Create Article') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <form class="" id="sort_blogs" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col text-center text-md-left">
                <h5 class="mb-md-0 h6">{{ translate('All blog posts') }}</h5>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <input type="text" class="form-control form-control-sm" id="search" name="search"@isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type & Enter') }}">
                </div>
            </div>
        </div>
        </form>
        <div class="card-body">
            <table class="table mb-0 aiz-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{translate('Title')}}</th>
                        <th data-breakpoints="lg">{{translate('Category')}}</th>
                        <th data-breakpoints="lg">{{translate('Short Description')}}</th>
                        @if(get_setting('portfolio_landing'))
                        <th data-breakpoints="lg">{{translate('News')}}</th>
                        <th data-breakpoints="lg">{{translate('Event')}}</th>
                        <th data-breakpoints="lg" class="w-80px">{{translate('Going On')}}</th>
                        @endif
                        <th data-breakpoints="lg">{{translate('Status')}}</th>
                        <th class="text-right">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blogs as $key => $blog)
                    <tr>
                        <td>
                            {{ ($key+1) + ($blogs->currentPage() - 1) * $blogs->perPage() }}
                        </td>
                        <td>
                            {{ $blog->title }}
                        </td>
                        <td>
                            @if($blog->category != null)
                                {{ $blog->category->category_name }}
                            @else
                                --
                            @endif
                        </td>
                        <td>
                            {{ $blog->short_description }}
                        </td>
                        @if(get_setting('portfolio_landing'))
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input data-field="news" type="checkbox"
                                    @can('publish_blog') onchange="change_status(this)" @endcan
                                    value="{{ $blog->id }}"
                                    <?php if($blog->news == 1) echo "checked";?>
                                    @cannot('publish_blog') disabled @endcan
                                >
                                <span></span>
                            </label>
                        </td>

                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input data-field="event" type="checkbox"
                                    @can('publish_blog') onchange="change_status(this)" @endcan
                                    value="{{ $blog->id }}"
                                    <?php if($blog->event == 1) echo "checked";?>
                                    @cannot('publish_blog') disabled @endcan
                                >
                                <span></span>
                            </label>
                        </td>

                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input data-field="going_on" type="checkbox"
                                    @can('publish_blog') onchange="change_status(this)" @endcan
                                    value="{{ $blog->id }}"
                                    <?php if($blog->going_on == 1) echo "checked";?>
                                    @cannot('publish_blog') disabled @endcan
                                >
                                <span></span>
                            </label>
                        </td>
                        @endif
                        <td>
                            <label class="aiz-switch aiz-switch-success mb-0">
                                <input data-field="status" type="checkbox"
                                    @can('publish_blog') onchange="change_status(this)" @endcan
                                    value="{{ $blog->id }}"
                                    <?php if($blog->status == 1) echo "checked";?>
                                    @cannot('publish_blog') disabled @endcan
                                >
                                <span></span>
                            </label>
                        </td>
                        <td class="text-right">
                            @can('edit_blog')
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('blog.edit',$blog->id)}}" title="{{ translate('Edit') }}">
                                    <i class="las la-pen"></i>
                                </a>
                            @endcan
                            @can('delete_blog')
                                <a href="#" class="btn btn-soft-danger btn-icon btn-circle btn-sm confirm-delete" data-href="{{route('blog.destroy', $blog->id)}}" title="{{ translate('Delete') }}">
                                    <i class="las la-trash"></i>
                                </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $blogs->appends(request()->input())->links() }}
            </div>
        </div>
</div>

@endsection

@section('modal')
    @include('modals.delete_modal')
@endsection


@section('script')

    <script type="text/javascript">
        function change_status(el){

            if('{{env('DEMO_MODE')}}' == 'On'){
                AIZ.plugins.notify('info', '{{ translate('Data can not change in demo mode.') }}');
                return;
            }

            var status = 0;
            let field  = el.dataset.field;
            if(el.checked){
                var status = 1;
            }
            $.post('{{ route('blog.change-status') }}', {_token:'{{ csrf_token() }}', id:el.value,field: field, status:status}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('Blog') }} ' + field + ' {{ translate('updated successfully') }}');
                }
                else{
                    AIZ.plugins.notify('danger', '{{ translate('Something went wrong') }}');
                }
            });
        }

    </script>

@endsection
