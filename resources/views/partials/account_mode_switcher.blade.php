@if (can_switch_account_mode())
    @php
        $currentMode = active_account_mode();
        $targetMode = $currentMode === 'seller' ? 'buyer' : 'seller';
        $label = $currentMode === 'seller' ? translate('Switch to Buyer') : translate('Switch to Seller');
    @endphp
    <form method="POST" action="{{ route('account-mode.switch') }}" class="d-inline-block m-0" data-account-mode-switcher>
        @csrf
        <input type="hidden" name="mode" value="{{ $targetMode }}">
        <button type="submit" class="btn btn-sm btn-soft-primary rounded-0 fw-600" data-account-mode="{{ $currentMode }}">
            <i class="las la-exchange-alt mr-1"></i>
            {{ $label }}
        </button>
    </form>
@endif
