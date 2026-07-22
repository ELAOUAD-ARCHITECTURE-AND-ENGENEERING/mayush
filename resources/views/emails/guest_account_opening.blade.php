<p>{{ translate('Hi! An account has been created on').' '.get_setting('site_name', config('app.name', 'MAYUSH DESIGN')) }}</p>
<p>{{ translate('Your Email is') }}: {{ $email }}</p>
<p>{{ translate('Your Password is') }}: {{ $password }}</p>
<a class="btn btn-primary btn-md" href="{{ config('app.url', url('/')) }}">{{ translate('Go to the website') }}</a>