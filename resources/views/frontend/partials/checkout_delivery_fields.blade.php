<hr>
<h6 class="fw-700 mb-3">{{ translate('First delivery address') }}</h6>

@php($field_prefix = $field_prefix ?? 'checkout-delivery')
@php($defaultCountryCode = $defaultCountryCode ?? '+212')

<div class="form-group">
    <label for="{{ $field_prefix }}-address">{{ translate('Address') }}</label>
    <textarea id="{{ $field_prefix }}-address" class="form-control" name="delivery_address" rows="2" required></textarea>
</div>

<div class="row gutters-10">
    <div class="col-md-6">
        <div class="form-group">
            <label for="{{ $field_prefix }}-country">{{ translate('Country') }}</label>
            <select id="{{ $field_prefix }}-country" class="form-control checkout-country-select" name="delivery_country_id" required>
                <option value="">{{ translate('Select your country') }}</option>
                @foreach ($activeCountries as $country)
                    <option value="{{ $country->id }}" data-phone-code="{{ $country->phonecode ?? '' }}" {{ $defaultCountry && $defaultCountry->id == $country->id ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    @if(get_setting('has_state') == 1)
        <div class="col-md-6">
            <div class="form-group">
            <label for="{{ $field_prefix }}-state">{{ translate('State') }}</label>
            <select id="{{ $field_prefix }}-state" class="form-control checkout-state-select" name="delivery_state_id" required>
                    <option value="">{{ translate('Select your state') }}</option>
                </select>
            </div>
        </div>
    @endif
    <div class="col-md-6">
        <div class="form-group">
            <label for="{{ $field_prefix }}-city">{{ translate('City') }}</label>
            <select id="{{ $field_prefix }}-city" class="form-control checkout-city-select" name="delivery_city_id" required>
                <option value="">{{ translate('Select your city') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="{{ $field_prefix }}-area">{{ translate('Area') }}</label>
            <select id="{{ $field_prefix }}-area" class="form-control checkout-area-select" name="delivery_area_id">
                <option value="">{{ translate('Select your area') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label for="{{ $field_prefix }}-postal-code">{{ translate('Postal code') }}</label>
            <input id="{{ $field_prefix }}-postal-code" type="text" class="form-control" name="delivery_postal_code" autocomplete="postal-code">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="{{ $field_prefix }}-country-code">{{ translate('Country code') }}</label>
            <input id="{{ $field_prefix }}-country-code" type="text" class="form-control checkout-delivery-country-code" name="delivery_country_code" autocomplete="tel-country-code" value="{{ $defaultCountryCode }}" required>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label for="{{ $field_prefix }}-phone">{{ translate('Delivery phone') }}</label>
            <input id="{{ $field_prefix }}-phone" type="text" class="form-control" name="delivery_phone" autocomplete="tel" required>
        </div>
    </div>
</div>
