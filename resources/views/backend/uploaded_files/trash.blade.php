@extends('backend.layouts.app')

@section('content')
<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('Trash - Deleted Files')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('uploaded-files.index') }}" class="btn btn-circle btn-info">
                <i class="las la-arrow-left"></i>
				<span>{{translate('Back to All Files')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
	<form id="sort_uploads" action="">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-0 h6">{{translate('Trash Content')}}</h5>
            </div>
			<div class="dropdown mb-2 mb-md-0">
                <button class="btn border dropdown-toggle" type="button" data-toggle="dropdown">
                    {{translate('Bulk Action')}}
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="javascript:void(0)" onclick="bulk_restore()"> {{translate('Restore selection')}}</a>
                    <a class="dropdown-item" href="javascript:void(0)" onclick="showBulkForceDeleteModal()"> {{translate('Delete permanently')}}</a>
                </div>
            </div>
            <div class="mx-3 d-none d-md-block align-self-center">
				<span class="badge badge-inline badge-info p-2" id="selection-count">{{ translate('0 files selected') }}</span>
			</div>
            <div class="col-md-3 ml-auto mr-0">
                <select class="form-control form-control-xs aiz-selectpicker" name="sort" onchange="sort_uploads()">
                    <option value="newest" @if($sort_by == 'newest') selected="" @endif>{{ translate('Sort by newest') }}</option>
                    <option value="oldest" @if($sort_by == 'oldest') selected="" @endif>{{ translate('Sort by oldest') }}</option>
                    <option value="smallest" @if($sort_by == 'smallest') selected="" @endif>{{ translate('Sort by smallest') }}</option>
                    <option value="largest" @if($sort_by == 'largest') selected="" @endif>{{ translate('Sort by largest') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-xs" name="search" placeholder="{{ translate('Search trash') }}" value="{{ $search }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
            </div>
        </div>
    
		<div class="card-body">
			<div class="form-group">
				<div class="aiz-checkbox-inline">
					<label class="aiz-checkbox">
						{{ translate('Select All')}}
						<input type="checkbox" class="check-all">
						<span class="aiz-square-check"></span>
					</label>
				</div>
			</div>

			<div class="row gutters-5">
				@foreach($all_uploads as $key => $file)
					@php
						if($file->file_original_name == null){
							$file_name = translate('Unknown');
						}else{
							$file_name = $file->file_original_name;
						}
						$file_path = my_asset($file->file_name);
						if($file->external_link) {
							$file_path = $file->external_link;
						}
						
					@endphp
					<div class="col-auto w-140px w-lg-220px">
						<div class="aiz-file-box">
							<div class="dropdown-file" >
								<a class="dropdown-link" data-toggle="dropdown">
									<i class="la la-ellipsis-v"></i>
								</a>
								<div class="dropdown-menu dropdown-menu-right">
									<a href="javascript:void(0)" class="dropdown-item" onclick="detailsInfo(this)" data-id="{{ $file->id }}">
										<i class="las la-info-circle mr-2"></i>
										<span>{{ translate('Details Info') }}</span>
									</a>
									<a href="javascript:void(0)" class="dropdown-item" onclick="restore_single({{ $file->id }})">
										<i class="las la-trash-restore mr-2"></i>
										<span>{{ translate('Restore') }}</span>
									</a>
									<a href="javascript:void(0)" class="dropdown-item" onclick="force_delete_single({{ $file->id }}, '{{ $file_name }}')">
										<i class="las la-trash mr-2"></i>
										<span>{{ translate('Delete Permanently') }}</span>
									</a>
								</div>
							</div>
							<div class="select-box">
								<div class="aiz-checkbox-inline">
									<label class="aiz-checkbox">
										<input type="checkbox" class="check-one" name="id[]" value="{{$file->id}}">
										<span class="aiz-square-check"></span>
									</label>
								</div>
							</div>
							<div class="card card-file aiz-uploader-select c-default" title="{{ $file_name }}.{{ $file->extension }}">
								<div class="card-file-thumb">
									@if($file->type == 'image')
										<img src="{{ $file_path }}" class="img-fit">
									@elseif($file->type == 'video')
										<i class="las la-file-video"></i>
									@else
										<i class="las la-file"></i>
									@endif
								</div>
								<div class="card-body">
									<h6 class="d-flex">
										<span class="text-truncate title">{{ $file_name }}</span>
										<span class="ext">.{{ $file->extension }}</span>
									</h6>
									<p>{{ formatBytes($file->file_size) }}</p>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
			<div class="aiz-pagination mt-3">
				{{ $all_uploads->appends(request()->input())->links() }}
			</div>
		</div>
	</form>
</div>
@endsection

@section('modal')
<div id="info-modal" class="modal fade">
	<div class="modal-dialog modal-dialog-right">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title h6">{{ translate('File Info') }}</h5>
				<button type="button" class="close" data-dismiss="modal">
				</button>
			</div>
			<div class="modal-body c-scrollbar-light position-relative" id="info-modal-content">
				<div class="c-preloader text-center absolute-center">
                    <i class="las la-spinner la-spin la-3x opacity-70"></i>
                </div>
			</div>
		</div>
	</div>
</div>

<!-- Bulk Force Delete modal -->
<div class="modal fade" id="bulk-force-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body text-center pt-0">
                <div class="mb-3">
                    <i class="las la-exclamation-triangle la-3x text-danger"></i>
                </div>
                <h4 class="h5 mb-2">{{ translate('Permanently Delete Files?') }}</h4>
                <p class="mb-0">{{ translate('Are you sure you want to permanently delete these files?') }}</p>
                <p class="text-danger small">{{ translate('This action cannot be undone and files will be removed from storage.') }}</p>
                <div id="selected-files-list-container" class="mt-3 text-left" style="max-height: 200px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="selected-files-list">
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0">
                <button type="button" class="btn btn-sm btn-light" data-dismiss="modal">{{ translate('Cancel') }}</button>
                <button type="button" class="btn btn-sm btn-danger px-3" onclick="bulk_force_delete()">{{ translate('Permanently Delete') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
	<script type="text/javascript">
		$(document).on("change", ".check-all", function() {
			if(this.checked) {
				$('.check-one:checkbox').each(function() {
					this.checked = true;
				});
			} else {
				$('.check-one:checkbox').each(function() {
					this.checked = false;
				});
			}
			updateSelectionCount();
		});

		$(document).on("change", ".check-one", function() {
			updateSelectionCount();
		});

		function updateSelectionCount() {
			var count = $('.check-one:checkbox:checked').length;
			$('#selection-count').html(count + ' ' + '{{ translate('files selected') }}');
		}

		function sort_uploads(el){
            $('#sort_uploads').submit();
        }

		function bulk_restore() {
            var count = $('.check-one:checkbox:checked').length;
			if(count == 0) {
				AIZ.plugins.notify('warning', '{{ translate('Please select at least one file.') }}');
                return;
			}
            var data = new FormData($('#sort_uploads')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('uploaded-files.restore')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Files restored successfully.') }}');
						location.reload();
                    }
					else{
						AIZ.plugins.notify('danger', '{{ translate('Something Went Wrong.') }}');
					}
                }
            });
        }

        function restore_single(id) {
            $.post("{{ route('uploaded-files.restore') }}", {_token: AIZ.data.csrf, id: [id]}, function(data){
                if(data == 1){
                    AIZ.plugins.notify('success', '{{ translate('File restored successfully.') }}');
                    location.reload();
                } else {
                    AIZ.plugins.notify('danger', '{{ translate('Something Went Wrong.') }}');
                }
            });
        }

        function showBulkForceDeleteModal() {
            var count = $('.check-one:checkbox:checked').length;
			if(count > 0) {
				var fileList = "";
				$('.check-one:checkbox:checked').each(function() {
					var fileName = $(this).closest('.aiz-file-box').find('.title').text();
					var fileExt = $(this).closest('.aiz-file-box').find('.ext').text();
					fileList += '<li class="list-group-item py-1"><i class="las la-file mr-2"></i>' + fileName + fileExt + '</li>';
				});
				$('#selected-files-list').html(fileList);
				$('#bulk-force-delete-modal').modal('show');
			} else {
				AIZ.plugins.notify('warning', '{{ translate('Please select at least one file.') }}');
			}
        }

        function bulk_force_delete() {
            var data = new FormData($('#sort_uploads')[0]);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('uploaded-files.bulk-force-delete')}}",
                type: 'POST',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                success: function (response) {
                    if(response == 1) {
                        AIZ.plugins.notify('success', '{{ translate('Files deleted permanently.') }}');
						location.reload();
                    }
					else{
						AIZ.plugins.notify('danger', '{{ translate('Something Went Wrong.') }}');
					}
                }
            });
        }

        function force_delete_single(id, name) {
            $('#selected-files-list').html('<li class="list-group-item py-1"><i class="las la-file mr-2"></i>' + name + '</li>');
            // We reuse the bulk force delete modal logic by setting the checkbox for this one only
            $('.check-one:checkbox').prop('checked', false);
            $('.check-one:checkbox[value="'+id+'"]').prop('checked', true);
            $('#bulk-force-delete-modal').modal('show');
        }

		function detailsInfo(e){
            $('#info-modal-content').html('<div class="c-preloader text-center absolute-center"><i class="las la-spinner la-spin la-3x opacity-70"></i></div>');
			var id = $(e).data('id')
			$('#info-modal').modal('show');
			$.post('{{ route('uploaded-files.info') }}', {_token: AIZ.data.csrf, id:id}, function(data){
                $('#info-modal-content').html(data);
			});
		}
	</script>
@endsection
