@extends('frontend.layouts.app')

@section('content')
<section class="pt-4 mb-4">
    <div class="container text-center">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4">{{ translate('Register your shop')}}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('shops.create') }}">"{{ translate('Register your shop')}}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="pt-4 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-xxl-5 col-xl-6 col-md-8 mx-auto">
                <form id="shop" class="" action="{{ route('shops.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if (!Auth::check())
                        <div class="bg-white rounded shadow-sm mb-3">
                            <div class="fs-15 fw-600 p-3 border-bottom">
                                {{ translate('Personal Info')}}
                            </div>
                            <div class="p-3">
                                <div class="form-group">
                                    <label>{{ translate('Your Name')}} <span class="text-primary">*</span></label>
                                    <input type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" value="{{ old('name') }}" placeholder="{{  translate('Name') }}" name="name">
                                </div>
                                <div class="form-group">
                                    <label>{{ translate('Your Email')}} <span class="text-primary">*</span></label>
                                    <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                                </div>
                                <div class="form-group my-1">
                                    <label>{{ translate('Your Password')}} <span class="text-primary">*</span></label>
                                    <div class="input_container position-relative">
                                        <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{  translate('Password') }}" name="password" id="pass">
                                        <div class="toggle-password" onclick="togglePasswordVisibility()">
                                            <svg
                                               id="lock"
                                                    class="eye-icon d-none"
                                                     width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                     <path fill-rule="evenodd" clip-rule="evenodd" d="M12 6C8.76722 6 5.95965 8.31059 4.2048 11.7955C4.17609 11.8526 4.15483 11.8948 4.1369 11.9316C4.12109 11.964 4.11128 11.9853 4.10486 12C4.11128 12.0147 4.12109 12.036 4.1369 12.0684C4.15483 12.1052 4.17609 12.1474 4.2048 12.2045C5.95965 15.6894 8.76722 18 12 18C15.2328 18 18.0404 15.6894 19.7952 12.2045C19.8239 12.1474 19.8452 12.1052 19.8631 12.0684C19.8789 12.036 19.8888 12.0147 19.8952 12C19.8888 11.9853 19.8789 11.964 19.8631 11.9316C19.8452 11.8948 19.8239 11.8526 19.7952 11.7955C18.0404 8.31059 15.2328 6 12 6ZM2.41849 10.896C4.35818 7.04403 7.7198 4 12 4C16.2802 4 19.6419 7.04403 21.5815 10.896C21.5886 10.91 21.5958 10.9242 21.6032 10.9389C21.6945 11.119 21.8124 11.3515 21.8652 11.6381C21.9071 11.8661 21.9071 12.1339 21.8652 12.3619C21.8124 12.6485 21.6945 12.8811 21.6032 13.0611C21.5958 13.0758 21.5886 13.09 21.5815 13.104C19.6419 16.956 16.2802 20 12 20C7.7198 20 4.35818 16.956 2.41849 13.104C2.41148 13.09 2.40424 13.0758 2.39682 13.0611C2.3055 12.881 2.18759 12.6485 2.13485 12.3619C2.09291 12.1339 2.09291 11.8661 2.13485 11.6381C2.18759 11.3515 2.3055 11.119 2.39682 10.9389C2.40424 10.9242 2.41148 10.91 2.41849 10.896ZM12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10ZM8.00002 12C8.00002 9.79086 9.79088 8 12 8C14.2092 8 16 9.79086 16 12C16 14.2091 14.2092 16 12 16C9.79088 16 8.00002 14.2091 8.00002 12Z" fill="#0F1729"/>
                                                     </svg>
                                          
                                                <svg id="unlock" class="eye-icon" width="20px" height="20px" 
                                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M4.51848 5.55639L6.82251 7.86611C5.6051 8.85592 4.65508 10.1872 4.09704 11.7195L4 11.9859L4.10384 12.2498C4.69454 13.7507 5.68174 15.1297 6.90031 16.1241C8.31938 17.2822 10.1044 17.9758 12.0449 17.9758C13.4414 17.9758 14.7584 17.6164 15.9164 16.9824L18.4277 19.5L19.4815 18.4436L17.1775 16.1339L16.1167 15.0705L9.19255 8.12922L8.08361 7.01755L5.57226 4.5L4.51848 5.55639ZM7.88326 8.92948C6.89207 9.69943 6.09644 10.7454 5.59957 11.9656C6.10925 13.1365 6.90095 14.1982 7.84116 14.9655C9.01025 15.9196 10.467 16.4819 12.0449 16.4819C13.0265 16.4819 13.9605 16.2644 14.8075 15.8708L13.875 14.9361C13.3341 15.2838 12.6902 15.4859 12 15.4859C10.0795 15.4859 8.52268 13.9252 8.52268 12C8.52268 11.3081 8.72429 10.6626 9.07117 10.1203L7.88326 8.92948ZM10.1701 11.2219C10.0688 11.4609 10.013 11.7237 10.013 12C10.013 13.1001 10.9026 13.9919 12 13.9919C12.2756 13.9919 12.5378 13.936 12.7762 13.8345L10.1701 11.2219Z" fill="#1F2328"/>
<path d="M11.9551 6.02417C11.2163 6.02417 10.4988 6.1248 9.81472 6.31407C9.69604 6.3469 9.57842 6.38239 9.4619 6.42047L10.6812 7.64274C11.0937 7.56094 11.5195 7.51813 11.9551 7.51813C13.533 7.51813 14.9898 8.08041 16.1588 9.03448C17.099 9.80176 17.8907 10.8635 18.4004 12.0344C18.0874 12.803 17.6557 13.503 17.1308 14.1083L18.1868 15.1669C18.9236 14.3372 19.5102 13.359 19.903 12.2805L20 12.0141L19.8962 11.7502C19.3055 10.2493 18.3183 8.87033 17.0997 7.87589C15.6806 6.71782 13.8956 6.02417 11.9551 6.02417Z" fill="#1F2328"/>
</svg>
                                              
                                          </div>
                                    </div>
                                    
                                    <span class="text-danger" id="complexityResult"></span>
                                    <div id="valider"></div>
                                    
                                </div>
                                <div class="form-group">
                                    <label>{{ translate('Repeat Password')}} <span class="text-primary">*</span></label>
                                    <input type="password" class="form-control" placeholder="{{  translate('Confirm Password') }}" name="password_confirmation">
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="bg-white rounded shadow-sm mb-4">
                        <div class="fs-15 fw-600 p-3 border-bottom">
                            {{ translate('Basic Info')}}
                        </div>
                        <div class="p-3">
                            <div class="form-group">
                                <label>{{ translate('Shop Name')}} <span class="text-primary">*</span></label>
                                <input type="text" class="form-control" placeholder="{{ translate('Shop Name')}}" name="shop_name" required>
                            </div>
                            <div class="form-group">
                                <label>{{ translate('Address')}} <span class="text-primary">*</span></label>
                                <input type="text" class="form-control mb-3" placeholder="{{ translate('Address')}}" name="address" required>
                            </div>
                            <div class="form-group">
                                <label>{{ translate('Type')}} <span class="text-primary">*</span></label>
                                <select type="text" class="seller-confirm select-type form-control mb-3" name="type" required>
                                    <option value="">{{ translate('Select') }}     ...</option>
                                    <option value="company" {{ old('type') == 'company' ? 'selected' : '' }}>{{ translate('company') }}</option>
                                    <option value="contractor" {{ old('type') == 'contractor' ? 'selected' : '' }}>{{ translate('contractor') }}</option>
                                    <option value="particular" {{ old('type') == 'particular' ? 'selected' : '' }}>{{ translate('Particular') }}</option>
                                </select>
                            </div>
                            <div class="form-group seller-confirm confirm-value"  style="display: {{ $errors->has('confirm') ? ' block' : 'none' }};">
                                <label confirm="company" style="display: {{ old('type') == 'company' ? 'block' : 'none' }};">{{ translate('Tax Identification (RC/IF/ICE)') }} <span class="text-primary">*</span></label>
                                <label confirm="contractor" style="display: {{ old('type') == 'contractor' ? 'block' : 'none' }};"
                                >{{ translate('CIN')}} <span class="text-primary">*</span></label>
                                <input type="text" class="form-control{{ $errors->has('confirm') ? ' is-invalid' : '' }}" placeholder="" value="{{ old('rc') }}" name="rc" required>
                            </div>
                            
                        </div>
                    </div>
                    <div class="bg-white rounded shadow-sm mb-4">
                    <div class="row mb-2">
                        <div class="col-12 ">
                            <label class="aiz-checkbox p-3">
                                
                                <input type="checkbox" name="agree" class="form-control{{ $errors->has('agree') ? ' is-invalid' : '' }}"  class="termscheck" required>
                                <p class="opacity-60 terms">{{  translate('In accordance with law 09-08, you have a right of access, rectification and opposition to the processing of your personal data. This processing has been authorized by the CNDP under the n°…....(in progress).') }}</p>

                                <p class="opacity-60 terms">{{  translate('I have read and accept the')}} 
                                <a class="text-reset text-primary" href="{{ route('terms') }}">{{ translate('Terms & conditions') }}</a>{{ translate(', in particular the mention relating to the protection of personal data.') }}</p>
                                {{-- <p class="opacity-60 terms">{{  translate('Conformément à la loi 09-08, vous disposez d’un droit d’accès, de rectification et d’opposition au traitement de vos données personnelles. Ce traitement a été autorisé par la CNDP sous le n°…....(en cours)') }}</p>
                                                        <p class="opacity-60 terms">{{  translate('J’ai lu et jaccepte les')}} 
                                                            <a class="text-reset text-primary" href="{{ route('terms') }}">{{ translate(' Conditions Générales d’Utilisation,') }}</a>{{ translate(' notamment la mention relative à la protection des données personnelles.') }}</p> --}}
                                
                            <span class="aiz-square-check termscheck"></span>
                            </label>
                        </div>
                     </div>
                    </div>
                    @if(get_setting('google_recaptcha') == 1)
                        <div class="form-group mt-2 mx-auto row">
                            <div class="g-recaptcha" data-sitekey="{{ env('CAPTCHA_KEY') }}"></div>
                        </div>
                    @endif

                    @if (get_setting('cloudflare_turnstile') == 1 && get_setting('turnstile_seller_register') == 1)
                        <div class="form-group mt-2 mx-auto row">
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                        </div>
                    @endif

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary fw-600">{{ translate('Register Your Shop')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@endsection

@section('script')
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script type="text/javascript">
    // making the CAPTCHA  a required field for form submission
    $(document).ready(function(){
        var passwordInput = $('#pass');
    var complexityResult = $('#complexityResult');
    $('#reg-form').submit(function(event) {

    var password = passwordInput.val();
    if(checkPasswordComplexity(password)===false){
    
      event.preventDefault();
      // Display an error message
      AIZ.plugins.notify('danger', '{{ translate('Please Enter a valide password') }}');
      
    }
    
  });
  
  function checkPasswordComplexity(password) {
    var requirements = [
    { regex: /.{8,}/, message: "Minimum of 08 characters" },
    { regex: /[A-Z]/, message: "One uppercase letter" },
    { regex: /[a-z]/, message: "One lowercase letter" },
    { regex: /\d/, message: "One digi" },
    { regex: /[!@#$%^&*]/, message: "One special character (!@#$%^&*)." }
  ];

  var validationMessage = $("#valider");
   validationMessage.empty();
    var error=0;
  for (var i = 0; i < requirements.length; i++) {
    if (!requirements[i].regex.test(password)) {
        error+=1;
     validationMessage.append('<li class="text-danger">' + requirements[i].message + '</li>');
    } else {
      validationMessage.append('<li class="text-success"><i class="las la-check mx-2"></i>' + requirements[i].message + '</li>');
    }
      
  }
  validationMessage.append("</ul>")
  if(error===requirements.length){
        return false;
    }
 

  }
  

  passwordInput.on('input', function() {
    
    var password = passwordInput.val();
    var result = checkPasswordComplexity(password);
    if(password.length>0){
        complexityResult.text(result);
    }else{
        complexityResult.text(""); 
    }
    
  });
 
        // alert('helloman');
        $("#shop").on("submit", function(evt)
        {
            var response = grecaptcha.getResponse();
            if(response.length == 0)
            {
            //reCaptcha not verified
                alert("{{ translate('Please verify you are human!') }}");
                evt.preventDefault();
                return false;
            }
            //captcha verified
            //do the rest of your validations here
            $("#reg-form").submit();
        });
    });
        $(window).scroll(function() {
        if ($(document).scrollTop() > 500) {
            $('.category-menu-icon-box').removeClass('d-none');
        }
        else {
            $('.category-menu-icon-box').addClass('d-none');
        }
    });
    $(window).scroll(function() {
        if ($(document).scrollTop() > 500) {
            $('.category-menu-icon-box').removeClass('d-none');
        }
        else {
            $('.category-menu-icon-box').addClass('d-none');
        }
    });
    
    
    /// Seller Confirmation
    
    $(".seller-confirm.select-type").change(function() {
        
        if($(this).val() == 'company') {
            $(".seller-confirm.confirm-value").css({
                display: "block"
            })
            
            $(".seller-confirm.confirm-value label").css({
                display: "none"
            })
            
            $(".seller-confirm.confirm-value label[confirm='company']").css({
                display: "block"
            })
        }
        
        else if($(this).val() == 'contractor'  || $(this).val() == 'particular') {
            $(".seller-confirm.confirm-value").css({
                display: "block"
            })
            
            $(".seller-confirm.confirm-value label").css({
                display: "none"
            })
            
            $(".seller-confirm.confirm-value label[confirm='contractor']").css({
                display: "block"
            })
            
        } else {
            
            $(".seller-confirm.confirm-value").css({
                display: "none"
            })
        }
    })
    
    
function show_password(event) {
    const elem = $(event.currentTarget).parent().find("input").eq(0);
    if (elem.attr('type') === 'text') {
        elem.attr({ type: 'password' });
        $(event.currentTarget).attr({class:'las la-eye show-password-eye'})
    }
    else {
        elem.attr({ type: 'text' });
        $(event.currentTarget).attr({class:'las la-eye-slash show-password-eye'})
    }
}
    
   
</script>
@endsection
