<?php

namespace App\Exceptions;

use App\Utility\NgeniusUtility;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e)) {
                try {
                    \App\Models\HealthMetric::create([
                        'type' => 'error',
                        'source' => 'backend',
                        'message' => $e->getMessage(),
                        'context' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => substr($e->getTraceAsString(), 0, 1000),
                        ],
                        'created_at' => now(),
                    ]);
                } catch (Throwable $reportError) {
                    // Fail silently to avoid recursion
                }
            }
        });

        // Global Security Monitoring: Log 403 Access Denied moved to render()
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof Redirectingexception) {
            return redirect()->back();
        }

        // Handle CSRF token mismatch (expired session / stale form)
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your session has expired. Please refresh the page and try again.'], 419);
            }
            flash(translate('Your session has expired. Please try again.'))->warning();
            return redirect()->back()->withInput();
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 403) {
            try {
                \App\Models\AuditLog::create([
                    'admin_user_id' => auth()->id(),
                    'action_type' => 'UNAUTHORIZED_ACCESS',
                    'description' => "Unauthorized access attempt to " . $request->fullUrl(),
                    'ip_address' => $request->ip(),
                ]);
                
                event(new \App\Events\SecurityAlert("🚫 *Unauthorized Access* attempt.\n*URL:* `{$request->fullUrl()}`\n*User:* " . (auth()->user() ? auth()->user()->email : 'Guest') . "\n*IP:* `{$request->ip()}`", 'warning'));
            } catch (Throwable $auditError) {
                // Fail silently
            }
        }

        if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException || 
            ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() === 429)) {
            try {
                $ip = $request->ip();
                $path = $request->path();
                $cacheKey = "throttle_audit_{$ip}_{$path}";

                if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(15));
                    
                    $isSensitive = $request->is(
                        'admin/login*', 
                        'users/login*', 
                        'seller/login*', 
                        'deliveryboy/login*', 
                        'password/reset*', 
                        'checkout*', 
                        'express-buy*', 
                        'cmi/callback', 
                        'webhooks/onessta', 
                        '*/upload*'
                    );

                    if ($isSensitive) {
                        \App\Models\AuditLog::create([
                            'admin_user_id' => auth()->id(),
                            'action_type' => 'RATE_LIMIT_EXCEEDED',
                            'description' => "Rate limit exceeded on sensitive route: " . $request->fullUrl(),
                            'ip_address' => $ip,
                        ]);

                        event(new \App\Events\SecurityAlert("⏳ *Rate Limit Exceeded (Sensitive)*.\n*URL:* `{$request->fullUrl()}`\n*User:* " . (auth()->user() ? auth()->user()->email : 'Guest') . "\n*IP:* `{$ip}`", 'warning'));
                    }
                }
                
                $logCacheKey = "throttle_log_{$ip}_{$path}";
                if (!\Illuminate\Support\Facades\Cache::has($logCacheKey)) {
                    \Illuminate\Support\Facades\Cache::put($logCacheKey, true, now()->addMinutes(1));
                    \Illuminate\Support\Facades\Log::channel('security')->warning('Rate limit exceeded', [
                        'url' => $request->fullUrl(),
                        'ip' => $ip,
                        'user_id' => auth()->id(),
                        'payload_keys' => array_keys($request->except(['password', 'password_confirmation', 'credit_card', 'cvv', 'signature', 'payload'])),
                    ]);
                }
            } catch (Throwable $auditError) {
                // Fail silently
            }
        }

        if($this->isHttpException($e))
        {
            if ($request->is('customer-products/admin')) {
                return NgeniusUtility::initPayment();
            }
            
            return parent::render($request, $e);
        }
        else
        {
            return parent::render($request, $e);
        }
    }
}