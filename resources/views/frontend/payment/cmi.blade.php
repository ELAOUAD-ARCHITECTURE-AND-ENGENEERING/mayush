<!DOCTYPE html>
<html>
<head>
    <title>{{ translate('Redirecting to CMI...') }}</title>
</head>
<body onload="document.cmi_form.submit()">
    <div style="text-align: center; margin-top: 100px;">
        <h3>{{ translate('Please wait, redirecting to payment gateway...') }}</h3>
        <p>{{ translate('If you are not redirected automatically, please click the button below.') }}</p>
        <button onclick="document.cmi_form.submit()">{{ translate('Click here to pay') }}</button>
    </div>
    <form name="cmi_form" method="post" action="{{ $actionUrl }}">
        @foreach($data as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>
</body>
</html>
