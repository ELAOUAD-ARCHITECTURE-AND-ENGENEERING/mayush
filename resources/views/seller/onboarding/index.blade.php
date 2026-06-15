@extends('seller.layouts.app')

@section('panel_content')
    <div class="aiz-titlebar mt-2 mb-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="h3">{{ translate('Account Verification') }}</h1>
            </div>
        </div>
    </div>

    <!-- Stepper Indicator -->
    <div class="card mb-4">
        <div class="card-body">
            <ul class="nav nav-tabs nav-fill border-0">
                <li class="nav-item">
                    <span class="nav-link bg-success text-white rounded-left border-0"><i class="las la-check mr-2"></i>{{ translate('1. Register') }}</span>
                </li>
                <li class="nav-item">
                    @php $bg = in_array($shop->approval_status, ['under_review', 'approved']) ? 'bg-success' : 'bg-primary'; @endphp
                    <span class="nav-link {{ $bg }} text-white border-0">
                        @if(in_array($shop->approval_status, ['under_review', 'approved'])) <i class="las la-check mr-2"></i> @endif
                        {{ translate('2. Upload Documents') }}
                    </span>
                </li>
                <li class="nav-item">
                    @php $bg = $shop->approval_status == 'approved' ? 'bg-success' : ($shop->approval_status == 'under_review' ? 'bg-primary' : 'bg-secondary'); @endphp
                    <span class="nav-link {{ $bg }} text-white border-0">
                        @if($shop->approval_status == 'approved') <i class="las la-check mr-2"></i> @endif
                        {{ translate('3. Review') }}
                    </span>
                </li>
                <li class="nav-item">
                    <span class="nav-link {{ $shop->approval_status == 'approved' ? 'bg-success text-white' : 'bg-secondary text-white' }} rounded-right border-0">{{ translate('4. Approved') }}</span>
                </li>
            </ul>
        </div>
    </div>

    @if($shop->approval_status === 'under_review')
        <div class="alert alert-info">
            <h4 class="alert-heading"><i class="las la-info-circle"></i> {{ translate('Under Review') }}</h4>
            <p class="mb-0">{{ translate('Your documents were submitted on') }} <strong>{{ $shop->documents_submitted_at->format('d M, Y h:i A') }}</strong>. {{ translate('Our team is reviewing your application. You will be notified via email once approved.') }}</p>
        </div>
    @elseif($shop->approval_status === 'rejected')
        <div class="alert alert-danger">
            <h4 class="alert-heading"><i class="las la-times-circle"></i> {{ translate('Application Rejected') }}</h4>
            <p>{{ translate('Unfortunately, your application was rejected for the following reason:') }}</p>
            <blockquote class="blockquote border-left border-danger pl-3 mb-3">
                <p class="mb-0 text-dark">{{ $shop->rejection_reason }}</p>
            </blockquote>
            
            @if($shop->canResubmit())
                <p class="mb-0 font-weight-bold">{{ translate('You have') }} {{ max(0, 10 - $shop->resubmission_count) }} {{ translate('resubmission attempts remaining. Please correct the issues and re-upload your documents below.') }}</p>
            @else
                <p class="mb-0 font-weight-bold">{{ translate('You have exceeded the maximum number of resubmission attempts. Please contact support.') }}</p>
            @endif
        </div>
    @else
        <div class="alert alert-primary">
            <h4 class="alert-heading"><i class="las la-file-upload"></i> {{ translate('Document Upload Required') }}</h4>
            <p class="mb-0">{{ translate('To activate your seller account and start listing products, you must upload the following documents. Allowed formats: PDF, JPG, PNG (Max 10MB per file).') }}</p>
        </div>
    @endif

    @if(in_array($shop->approval_status, ['pending', 'rejected']) && $shop->canResubmit())
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{ translate('Upload Onboarding Documents') }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ $shop->approval_status === 'rejected' ? route('seller.onboarding.resubmit') : route('seller.onboarding.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Contract -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">{{ translate('1. Signed MayushSeller Contract') }} <span class="text-danger">*</span></label>
                                <p class="text-muted fs-12 mb-2">{{ translate('Please download the contract, sign it, and upload the scanned copy.') }}</p>
                                <a href="{{ route('seller.onboarding.contract') }}" class="btn btn-outline-primary btn-sm mb-3" target="_blank"><i class="las la-download"></i> {{ translate('Download Contract Template') }}</a>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="contract" id="contract" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label class="custom-file-label" for="contract">{{ translate('Choose file') }}</label>
                                </div>
                                @error('contract') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                            </div>

                            <!-- Government ID -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">{{ translate('2. Government-Issued Photo ID') }} <span class="text-danger">*</span></label>
                                <p class="text-muted fs-12 mb-2">{{ translate('Upload a clear copy of your National ID, Passport, or Driver\'s License.') }}</p>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="government_id" id="government_id" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label class="custom-file-label" for="government_id">{{ translate('Choose file') }}</label>
                                </div>
                                @error('government_id') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <!-- Business Registration -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">{{ translate('3. Business Registration Documents') }} <span class="text-danger">*</span></label>
                                <p class="text-muted fs-12 mb-2">{{ translate('Upload your company registration, tax certificate, or equivalent business proof.') }}</p>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="business_registration" id="business_registration" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <label class="custom-file-label" for="business_registration">{{ translate('Choose file') }}</label>
                                </div>
                                @error('business_registration') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                            </div>

                            <!-- Certification (Optional) -->
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">{{ translate('4. Professional Certification') }} <span class="text-muted">({{ translate('Optional') }})</span></label>
                                <p class="text-muted fs-12 mb-2">{{ translate('Any relevant certifications for your business category.') }}</p>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="certification" id="certification" accept=".pdf,.jpg,.jpeg,.png">
                                    <label class="custom-file-label" for="certification">{{ translate('Choose file') }}</label>
                                </div>
                                @error('certification') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">{{ translate('Submit Documents for Review') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@section('script')
<script>
    // Update custom file label with selected file name
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
</script>
@endsection
