<hr>
<h6 class="fw-700 mb-3">{{ translate('First delivery address') }}</h6>

<div class="form-group">
    <label>{{ translate('Address') }}</label>
    <textarea class="form-control" name="delivery_address" rows="2" required></textarea>
</div>

<div class="row gutters-10">
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ translate('Country') }}</label>
            <select class="form-control checkout-country-select" name="delivery_country_id" required>
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
                <label>{{ translate('State') }}</label>
                <select class="form-control checkout-state-select" name="delivery_state_id" required>
                    <option value="">{{ translate('Select your state') }}</option>
                </select>
            </div>
        </div>
    @endif
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ translate('City') }}</label>
            <select class="form-control checkout-city-select" name="delivery_city_id" required>
                <option value="">{{ translate('Select your city') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>{{ translate('Area') }}</label>
            <select class="form-control checkout-area-select" name="delivery_area_id">
                <option value="">{{ translate('Select your area') }}</option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>{{ translate('Postal code') }}</label>
            <input type="text" class="form-control" name="delivery_postal_code">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>{{ translate('Country code') }}</label>
            <input type="text" class="form-control checkout-delivery-country-code" name="delivery_country_code" value="{{ $defaultCountry->phonecode ?? '' }}" required>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label>{{ translate('Delivery phone') }}</label>
            <input type="text" class="form-control" name="delivery_phone" required>
        </div>
    </div>
</div>
