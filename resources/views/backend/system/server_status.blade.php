@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-10 col-xxl-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">{{ translate('Server information') }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped aiz-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Current Version') }}</th>
                            <th data-breakpoints="lg">{{ translate('Required Version') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ translate('PHP version') }}</td>
                            <td>{{ phpversion() }}</td>
                            <td>8.2</td>
                            <td>
                                @if (floatval(phpversion()) >= 8.2)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>MySQL</td>
                            <td>
                                @php
                                    $results = DB::select( "select version()" );
                                    $mysql_version =  $results[0]->{'version()'};
                                    $version_explode = explode("-",$mysql_version);
                                    $mysql_required_version = '8.0';
                                    if (isset($version_explode[1]) && $version_explode[1]=='MariaDB') {
                                        $mysql_required_version = '10.3';
                                    }
                                @endphp
                                {{ $mysql_version }}
                            </td>
                            <td>{{ $mysql_required_version }}+</td>
                            <td>
                                @if (floatval($version_explode[0]) >= floatval($mysql_required_version))
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">{{ translate('php.ini Config') }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped aiz-table">
                    <thead>
                        <tr>
                            <th>{{ translate('Config Name') }}</th>
                            <th data-breakpoints="lg">{{ translate('Current') }}</th>
                            <th data-breakpoints="lg">{{ translate('Recommended') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>file_uploads</td>
                            <td>
                                @if(ini_get('file_uploads') == 1)
                                {{ translate('On') }}
                                @else
                                {{ translate('Off') }}
                                @endif
                            </td>
                            <td>{{ translate('On') }}</td>
                            <td>
                                @if (ini_get('file_uploads') == 1)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_file_uploads</td>
                            <td>
                                {{ ini_get('max_file_uploads') }}
                            </td>
                            <td>20+</td>
                            <td>
                                @if (ini_get('max_file_uploads') >= 20)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>upload_max_filesize</td>
                            <td>
                                {{ ini_get('upload_max_filesize') }}
                            </td>
                            <td>128M+</td>
                            <td>
                                @if (str_replace(['M','G'],"", ini_get('upload_max_filesize')) >= 128)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>post_max_size</td>
                            <td>
                                {{ ini_get('post_max_size') }}
                            </td>
                            <td>128M+</td>
                            <td>
                                @if (str_replace(['M','G'],"", ini_get('post_max_size')) >= 128)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>allow_url_fopen</td>
                            <td>
                                @if(ini_get('allow_url_fopen') == 1)
                                {{ translate('On') }}
                                @else
                                {{ translate('Off') }}
                                @endif
                            </td>
                            <td>{{ translate('On') }}</td>
                            <td>
                                @if (ini_get('allow_url_fopen') == 1)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_execution_time</td>
                            <td>
                                @if(ini_get('max_execution_time') == '-1')
                                Unlimited
                                @else
                                {{ ini_get('max_execution_time') }}
                                @endif
                            </td>
                            <td>600+</td>
                            <td>
                                @if (ini_get('max_execution_time') == -1 || ini_get('max_execution_time') >= 600)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_input_time</td>
                            <td>
                                @if(ini_get('max_input_time') == '-1')
                                Unlimited
                                @else
                                {{ ini_get('max_input_time') }}
                                @endif
                            </td>
                            <td>120+</td>
                            <td>
                                @if (ini_get('max_input_time') == -1 || ini_get('max_input_time') >= 120)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>max_input_vars</td>
                            <td>
                                {{ ini_get('max_input_vars') }}
                            </td>
                            <td>1000+</td>
                            <td>
                                @if (ini_get('max_input_vars') >= 1000)
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>memory_limit</td>
                            <td>
                                @if(ini_get('memory_limit') == '-1')
                                Unlimited
                                @else
                                {{ ini_get('memory_limit') }}
                                @endif
                            </td>
                            <td>256M+</td>
                            <td>
                                @php
                                    $memory_limit = ini_get('memory_limit');
                                    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
                                        if ($matches[2] == 'G') {
                                            $memory_limit = $matches[1] * 1024 * 1024 * 1024; // nnnM -> nnn GB
                                        } else if ($matches[2] == 'M') {
                                            $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                                        } else if ($matches[2] == 'K') {
                                            $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                                        }
                                    }
                                @endphp
                                @if (ini_get('memory_limit') == -1 || $memory_limit >= (256 * 1024 * 1024))
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">{{ translate('Extensions information') }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('Extension Name') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    @php
                    $loaded_extensions = get_loaded_extensions();
                    $required_extensions = ['bcmath', 'ctype', 'json', 'mbstring', 'zip', 'zlib', 'openssl', 'tokenizer', 'xml', 'dom',  'curl', 'fileinfo', 'gd', 'pdo_mysql', 'sockets']
                    @endphp
                    <tbody>
                        @foreach ($required_extensions as $extension)
                        <tr>
                            <td>{{ $extension }}</td>
                            <td>
                                @if(in_array($extension, $loaded_extensions))
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">{{ translate('Filesystem Permissions') }}</h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('File or Folder') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    @php
                    $required_paths = ['.env', 'public', 'app/Providers', 'app/Http/Controllers', 'storage', 'resources/views']
                    @endphp
                    <tbody>
                        @foreach ($required_paths as $path)
                        <tr>
                            <td>{{ $path }}</td>
                            <td>
                                @if(is_writable(base_path($path)))
                                <i class="las la-check text-success"></i>
                                @else
                                <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">{{ translate('Security & Malware Scanner (ClamAV)') }}</h3>
            </div>
            <div class="card-body">
                @php
                    $clam_host = env('CLAMAV_HOST', '127.0.0.1');
                    $clam_port = env('CLAMAV_PORT', 3310);
                    $clam_disabled = env('DISABLE_CLAMAV', false);
                    
                    $socket_ext_ok = function_exists('socket_create');
                    $connection_ok = false;
                    $conn_error = "Not tested";

                    if (!$clam_disabled && $socket_ext_ok) {
                        try {
                            $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                            if ($socket) {
                                socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
                                if (@socket_connect($socket, $clam_host, $clam_port)) {
                                    $connection_ok = true;
                                    socket_close($socket);
                                } else {
                                    $conn_error = socket_strerror(socket_last_error());
                                }
                            }
                        } catch (\Exception $e) {
                            $conn_error = $e->getMessage();
                        }
                    }
                @endphp
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ translate('Check') }}</th>
                            <th>{{ translate('Value / Setting') }}</th>
                            <th>{{ translate('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ translate('Scanner Status') }}</td>
                            <td>
                                @if($clam_disabled)
                                    <span class="badge badge-inline badge-danger">{{ translate('DISABLED (Insecure)') }}</span>
                                @else
                                    <span class="badge badge-inline badge-success">{{ translate('ENABLED') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(!$clam_disabled)
                                    <i class="las la-check text-success"></i>
                                @else
                                    <i class="las la-exclamation-triangle text-warning" title="{{ translate('Malware scanning is currently turned off in .env') }}"></i>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>{{ translate('PHP Sockets Extension') }}</td>
                            <td>{{ $socket_ext_ok ? translate('Enabled') : translate('Missing') }}</td>
                            <td>
                                @if($socket_ext_ok)
                                    <i class="las la-check text-success"></i>
                                @else
                                    <i class="las la-times text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        @if(!$clam_disabled)
                        <tr>
                            <td>{{ translate('ClamAV Connectivity') }}</td>
                            <td>{{ $clam_host }}:{{ $clam_port }}</td>
                            <td>
                                @if($connection_ok)
                                    <i class="las la-check text-success"></i> <small class="text-success">{{ translate('Connected') }}</small>
                                @else
                                    <i class="las la-times text-danger"></i> <small class="text-danger">{{ translate('Refused') }} ({{ $conn_error }})</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                @if($clam_disabled)
                    <div class="alert alert-warning mt-3 mb-0">
                        {{ translate('Note: Malware scanning is disabled in your environment settings (DISABLE_CLAMAV=true). This prevents upload errors if the scanner is not installed, but reduces security.') }}
                    </div>
                @elseif(!$connection_ok)
                    <div class="alert alert-danger mt-3 mb-0">
                        {{ translate('Critical: The application is trying to use ClamAV but cannot connect to the daemon at') }} {{ $clam_host }}:{{ $clam_port }}. {{ translate('Please ensure ClamAV is running or disable it in .env if not supported.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
