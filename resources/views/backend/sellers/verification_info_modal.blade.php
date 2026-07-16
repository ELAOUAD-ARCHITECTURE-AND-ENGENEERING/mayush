<div class="modal-body">
    <h6 class="mb-3 font-weight-bold">{{ translate('Legacy Verification Workflow Disabled') }}</h6>
    <p class="text-muted mb-0">
        {{ translate('Review seller documents from the secure onboarding review interface.') }}
    </p>
    @if(isset($shop))
        <a href="{{ route('sellers.registration_pending', ['review_shop' => $shop->id]) }}" class="btn btn-primary mt-3">
            {{ translate('Open Onboarding Review') }}
        </a>
    @endif
</div>
