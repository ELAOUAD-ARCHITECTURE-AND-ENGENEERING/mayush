<!-- Express Buy Modal -->
<div class="modal fade" id="expressBuyModal" tabindex="-1" role="dialog" aria-labelledby="expressBuyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 rounded-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fs-18 fw-700 text-dark" id="expressBuyModalLabel">
                    ⚡ {{ translate('Express Buy (1-Click)') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center pt-2 pb-4 px-4">
                
                <!-- Eligible Content -->
                <div id="expressBuyEligible" class="d-none">
                    <div class="mb-4">
                        <div class="icon-circle bg-soft-success text-success mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-check-circle fs-32"></i>
                        </div>
                        <p class="fs-15 text-dark">{{ translate('You are eligible for 1-Click Purchase.') }}</p>
                    </div>
                    
                    <div class="bg-light p-3 rounded text-left mb-4 border">
                        <h6 class="fw-600 fs-13 text-secondary mb-2">{{ translate('Payment Method') }}</h6>
                        <p class="fs-14 fw-700 text-dark mb-3" id="eb_preferred_payment"></p>
                        
                        <h6 class="fw-600 fs-13 text-secondary mb-2">{{ translate('Shipping Address') }}</h6>
                        <p class="fs-14 text-dark mb-0">
                            <span class="fw-600 d-block" id="eb_address_name"></span>
                            <span id="eb_address_text"></span><br>
                            <span id="eb_address_phone"></span>
                        </p>
                    </div>
                    
                    <p class="text-muted fs-12 mb-4">{{ translate('By clicking confirm, your order will be placed immediately using these default settings.') }}</p>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light px-4 rounded-0" data-dismiss="modal">{{ translate('Cancel') }}</button>
                        <button type="button" class="btn btn-primary px-4 rounded-0" id="btnConfirmExpressBuy" style="background-color: #ff9900; border-color: #ff9900;">
                            {{ translate('Confirm & Buy Now') }}
                        </button>
                    </div>
                </div>

                <!-- Not Eligible Content -->
                <div id="expressBuyNotEligible" class="d-none">
                    <div class="mb-4">
                        <div class="icon-circle bg-soft-warning text-warning mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="las la-exclamation-circle fs-32"></i>
                        </div>
                        <h6 class="fs-16 fw-600 text-dark mb-2">{{ translate('Action Required') }}</h6>
                        <p class="fs-14 text-secondary">{{ translate('Please set a default address and payment method to use Express Buy.') }}</p>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light px-4 rounded-0 mr-2" data-dismiss="modal">{{ translate('Close') }}</button>
                        <a href="{{ route('profile') }}" class="btn btn-primary px-4 rounded-0">{{ translate('Go to Profile') }}</a>
                    </div>
                </div>

                <div id="expressBuyLoading" class="py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">{{ translate('Loading...') }}</span>
                    </div>
                    <p class="mt-3 text-secondary fs-13">{{ translate('Checking eligibility...') }}</p>
                </div>

            </div>
        </div>
    </div>
</div>
