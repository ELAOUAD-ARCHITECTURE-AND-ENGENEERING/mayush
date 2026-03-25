<script type="text/javascript">
   


    function submitShippingInfoForm(el) {
        var email = $("input[name='email']").val();
        var phone = $("input[name='country_code']").val()+$("input[name='phone']").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{(Route::has('guest_customer_info_check') ? Request::getBaseUrl().'/guest_customer_info_check' : '#')}}",
            type: 'POST',
            data: {
                email : email,
                phone : phone
            },
            success: function (response) {
                if(response ==  1){
                    $('#login_modal').modal();
                    AIZ.plugins.notify('warning', '{{ translate('You already have an account with this information. Please Login first.') }}');
                }
                else{
                    $('#shipping_info_form').submit();
                }
            }
        });
    }

    function add_new_address(){
        $('#new-address-modal').modal('show');
        // Trigger state loading if a country is already selected (e.g., hidden input or default)
        var country_id = $('#new-address-modal [name=country_id]').val();
        if(country_id) {
            console.log('Applying auto-load for country:', country_id);
            get_states(country_id);
        }
    }

     function add_new_billing_address(){
        $('#new-billing-address-modal').modal('show');
        // Trigger state loading if a country is already selected
        var country_id = $('#new-billing-address-modal [name=billing_country_id]').val();
        if(country_id) {
            console.log('Applying auto-load for billing country:', country_id);
            get_billing_states(country_id);
        }
    }

    function edit_address(address) {
        var url = '{{ Request::getBaseUrl() }}/addresses/:id/edit';
        url = url.replace(':id', address);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function (response) {
                $('#edit_modal_body').html(response.html);
                $('#edit-address-modal').modal('show');
                AIZ.plugins.bootstrapSelect('refresh');

                @if (get_setting('google_map') == 1)
                    var lat     = -33.8688;
                    var long    = 151.2195;

                    if(response.data.address_data.latitude && response.data.address_data.longitude) {
                        lat     = parseFloat(response.data.address_data.latitude);
                        long    = parseFloat(response.data.address_data.longitude);
                    }

                    initialize(lat, long, 'edit_');
                @endif
                @if(get_active_countries()->count() == 1)
                    if (response.data.address_data.country_id != {{ get_active_countries()->first()->id }}) {
                        get_states({{ get_active_countries()->first()->id }});
                    }
                @endif
            }
        });
    }

    function edit_billing_address(address) {
        var url = '{{ (Route::has('billing_addresses.edit') ? Request::getBaseUrl()."/billing_addresses/:id/edit" : '#') }}';
        url = url.replace(':id', address);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function (response) {
                $('#edit_modal_body').html(response.html);
                $('#edit-address-modal').modal('show');
                AIZ.plugins.bootstrapSelect('refresh');

                @if (get_setting('google_map') == 1)
                    var lat     = -33.8688;
                    var long    = 151.2195;

                    if(response.data.address_data.latitude && response.data.address_data.longitude) {
                        lat     = parseFloat(response.data.address_data.latitude);
                        long    = parseFloat(response.data.address_data.longitude);
                    }

                    initialize(lat, long, 'edit_');
                @endif
                @if(get_active_countries()->count() == 1)
                    if (response.data.address_data.country_id != {{ get_active_countries()->first()->id }}) {
                        get_states({{ get_active_countries()->first()->id }});
                    }
                @endif
            }
        });
    }

    $(document).on('change', '[name=country_id]', function() {
        var country_id = $(this).val();
        @if(get_setting('has_state') == 1)
            get_states(country_id);
        @else
            get_city_by_country(country_id);
        @endif
    });

    $(document).on('change', '[name=state_id]', function() {
        var state_id = $(this).val();
        get_city(state_id);
    });

    $(document).on('change', '[name=city_id]', function() {
        var city_id = $(this).val();
        get_area(city_id);
    });


    $(document).on('change', '[name=billing_country_id]', function() {
        var country_id = $(this).val();
        @if(get_setting('has_state') == 1)
            get_billing_states(country_id);
        @else
            get_billing_city_by_country(country_id);
        @endif
    });

    $(document).on('change', '[name=billing_state_id]', function() {
        var state_id = $(this).val();
        get_billing_city(state_id);
    });

    $(document).on('change', '[name=billing_city_id]', function() {
        var city_id = $(this).val();
        get_billing_area(city_id);
    });

    function get_states(country_id) {
        $('[name="state_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-states",
            type: 'POST',
            data: {
                country_id  : country_id
            },
            success: function (response) {
                console.log('States loaded response:', response);
                var obj = response;
                if(obj != '') {
                    $('[name="state_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                } else {
                    console.warn('States response was empty');
                }
            },
            error: function(xhr, status, error) {
                console.error('get_states AJAX Error:', status, error);
                alert('DEBUG ERROR: Failed to load states. Status: ' + status + '. ' + error);
            }
        });
    }

    function get_billing_states(country_id) {
        $('[name="billing_state_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-states",
            type: 'POST',
            data: {
                country_id  : country_id
            },
            success: function (response) {
                console.log('Billing states response:', response);
                var obj = response;
                if(obj != '') {
                    $('[name="billing_state_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            },
            error: function(xhr, status, error) {
                console.error('get_billing_states AJAX Error:', status, error);
                alert('DEBUG ERROR: Failed to load billing states. Status: ' + status);
            }
        });
    }



    function get_city(state_id) {
        $('[name="city_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-cities",
            type: 'POST',
            data: {
                state_id: state_id
            },
            success: function (response) {
                console.log('Cities response:', response);
                var obj = response;
                if(obj != ''&& $('<select></select>').html(obj).find('option').length > 1) {
                    $('[name="city_id"]').attr('disabled', false);
                    $('[name="city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }else{
                    $('[name="city_id"]').html('<option value="">{{ translate('No cities are available under this state.') }}</option>');
                    $('[name="city_id"]').attr('disabled', true);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            },
            error: function(xhr, status, error) {
                console.error('get_city AJAX Error:', status, error);
            }
        });
    }

    function get_billing_city(state_id) {
        $('[name="billing_city_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-cities",
            type: 'POST',
            data: {
                state_id: state_id
            },
            success: function (response) {
                console.log('Billing cities loaded:', response ? 'Yes' : 'No');
                var obj = response;
                if(obj != ''&& $('<select></select>').html(obj).find('option').length > 1) {
                    $('[name="billing_city_id"]').attr('disabled', false);
                    $('[name="billing_city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }else{
                    $('[name="billing_city_id"]').html('<option value="">{{ translate('No cities are available under this state.') }}</option>');
                    $('[name="billing_city_id"]').attr('disabled', true);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

    

    function get_area(city_id) {
        $('[name="area_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-areas",
            type: 'POST',
            data: {
                city_id: city_id
            },
            success: function (response) {
                console.log('Areas response:', response);
                var obj = response;
                $('[name="area_id"]').html(obj);
                AIZ.plugins.bootstrapSelect('refresh');
                if (obj.includes('<option') && !obj.includes('disabled selected')) {
                    $('[name="area_id"]').attr('required', true);
                    $('.area-field').removeClass('d-none'); 
                } else {
                    $('[name="area_id"]').removeAttr('required');
                    $('.area-field').addClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('get_area AJAX Error:', status, error);
            }
        });
    }


    function get_city_by_country(country_id){
        $('[name="city_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-cities-by-country",
            type: 'POST',
            data: {
                country_id: country_id
            },
            success: function (response) {
                var obj = response;
                if(obj != '' && $('<select></select>').html(obj).find('option').length > 1) {
                    $('[name="city_id"]').attr('disabled', false);
                    $('[name="city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }else{
                    $('[name="city_id"]').html('<option value="">{{ translate('No cities are available under this country.') }}</option>');
                    $('[name="city_id"]').attr('disabled', true);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }


     function get_billing_area(city_id) {
        $('[name="billing_area_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-areas",
            type: 'POST',
            data: {
                city_id: city_id
            },
            success: function (response) {
                console.log('Billing areas response:', response);
                var obj = response;
                $('[name="billing_area_id"]').html(obj);
                AIZ.plugins.bootstrapSelect('refresh');
                if (obj.includes('<option') && !obj.includes('disabled selected')) {
                    $('[name="billing_area_id"]').attr('required', true);
                    $('.billing-area-field').removeClass('d-none'); 
                } else {
                    $('[name="billing_area_id"]').removeAttr('required');
                    $('.billing-area-field').addClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                console.error('get_billing_area AJAX Error:', status, error);
            }
        });
    }


    function get_billing_city_by_country(country_id){
        $('[name="billing_city_id"]').html("");
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ Request::getBaseUrl() }}/get-cities-by-country",
            type: 'POST',
            data: {
                country_id: country_id
            },
            success: function (response) {
                var obj = response;
                if(obj != '' && $('<select></select>').html(obj).find('option').length > 1) {
                    $('[name="billing_city_id"]').attr('disabled', false);
                    $('[name="billing_city_id"]').html(obj);
                    AIZ.plugins.bootstrapSelect('refresh');
                }else{
                    $('[name="billing_city_id"]').html('<option value="">{{ translate('No cities are available under this country.') }}</option>');
                    $('[name="billing_city_id"]').attr('disabled', true);
                    AIZ.plugins.bootstrapSelect('refresh');
                }
            }
        });
    }

   
    $(document).on('change', '#sameAsShipping', function () {

        const billingTab  = $('#profile-tab');
        const billingPane = $('#billing-address');

        if (!billingTab.length || !billingPane.length) {
            return;
        }

        if (this.checked) {
            billingTab
                .addClass('disabled')
                .removeAttr('data-toggle')
                .attr('aria-disabled', 'true')
                .css('pointer-events', 'none');

            if (billingTab.hasClass('active')) {
                $('.nav-link:not(#profile-tab)').first().tab('show');
            }
            billingPane.find('input, textarea, select').each(function () {
                $(this).val('');
            });
            billingPane.find('[required]').each(function () {
                $(this).data('was-required', true).removeAttr('required');
            });

            billingPane.removeClass('show active').hide();

        } else {
            billingTab
                .removeClass('disabled')
                .attr('data-toggle', 'tab')
                .attr('aria-disabled', 'false')
                .css('pointer-events', '');
            billingPane.find('[data-was-required]').each(function () {
                $(this).attr('required', true).removeData('was-required');
            });
            billingPane.show();
        }
    });


    $(document).on('shown.bs.modal', '#new-address-modal, #new-billing-address-modal, #edit-address-modal', function () {
        AIZ.plugins.bootstrapSelect('refresh');
    });

</script>
