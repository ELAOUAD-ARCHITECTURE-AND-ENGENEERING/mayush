<div class="modal-body">
    <h6 class="mb-4 font-weight-bold">{{ translate('Verification Info') }}</h6>
    @if ($shop->verification_info != null)
    <table class="table inv-table-2" cellspacing="0" width="100%">
        <tbody>
            @foreach (json_decode($shop->verification_info) as $key => $info)
            <tr>
                <th class="text-muted">{{ $info->label }}</th>
                @if ($info->type == 'text' || $info->type == 'select' || $info->type == 'radio')
                <td>{{ $info->value }}</td>
                @elseif ($info->type == 'multi_select')
                <td>
                    {{ implode(', ', json_decode($info->value)) }}
                </td>
                @elseif ($info->type == 'file')
                <td>
                    <a href="{{ my_asset($info->value) }}" target="_blank" >{{translate('Click here')}}</a>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @if ($shop->verification_status != 1 && $shop->verification_info != null)
    <div class="text-center">
        <form action="{{ route('sellers.reject', $shop->id) }}" method="POST" class="d-inline-block">
            @csrf
            <input type="hidden" name="rejection_reason" value="{{ translate('Rejected from verification review.') }}">
            <button type="submit" class="btn btn-sm btn-danger d-innline-block">{{translate('Reject')}}</button>
        </form>
        <form action="{{ route('sellers.approve', $shop->id) }}" method="POST" class="d-inline-block">
            @csrf
            <button type="submit" class="btn btn-sm btn-success d-innline-block">{{translate('Accept')}}</button>
        </form>
    </div>
    @endif
</div>
