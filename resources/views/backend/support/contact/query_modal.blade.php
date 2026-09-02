
<div class="modal-header">
    <h5 class="modal-title h6">{{translate('Contact Query')}}</h5>
    <button type="button" class="close" data-dismiss="modal">
    </button>
</div>
<div class="modal-body">
    <table class="table table-striped table-bordered" >
        <tbody>
            <tr>
                <td>{{ translate('Name') }}</td>
                <td>{{ $contact->name }}</td>
            </tr>
            <tr>
                <td>{{ translate('Phone') }}</td>
                <td>{{ $contact->phone }}</td>
            </tr>
            <tr>
                <td>{{ translate('Email') }}</td>
                <td>{{ $contact->email }}</td>
            </tr>
            <tr>
                <td>{{ translate('Query') }}</td>
                <td>{!! nl2br(e($contact->content)) !!}</td>
            </tr>
            <tr>
                <td>{{ translate('Reply') }}</td>
                <td>{!! $contact->reply != null ? nl2br(e($contact->reply)) : translate('Not Replied Yet.') !!}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-footer">
    @can('reply_to_contact')
        @if ($contact->reply == null)
            <a href="javascript:void(1)" onclick="showReplyModal({{ $contact->id }})" class="btn btn-primary">{{translate('Reply')}}</a>
        @endif
    @endcan
    <button type="button" class="btn btn-light" data-dismiss="modal">{{translate('Cancel')}}</button>
</div>
