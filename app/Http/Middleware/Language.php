<?php

namespace App\Http\Middleware;

use App;
use Config;
use Closure;
use Session;
use Carbon\Carbon;
use App\Models\Language as LanguageModel;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Session::has('locale')){
            $locale = Session::get('locale');
        }
        else{
            $locale = $this->resolveDefaultLocale();
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $langcode = Session::has('langcode') ? Session::get('langcode') : $locale;
        Carbon::setLocale($langcode);

        return $next($request);
    }

    /**
     * Resolve the default locale from the database.
     * Priority: active default language → env DEFAULT_LANGUAGE → first active language → 'fr'
     */
    private function resolveDefaultLocale(): string
    {
        try {
            // 1. Check for a language explicitly marked as default and enabled
            $defaultLang = LanguageModel::where('status', 1)
                ->where('code', env('DEFAULT_LANGUAGE'))
                ->first();

            if ($defaultLang) {
                return $defaultLang->code;
            }

            // 2. Fallback: first enabled language in the system
            $firstActive = LanguageModel::where('status', 1)->first();
            if ($firstActive) {
                return $firstActive->code;
            }
        } catch (\Throwable $e) {
            // Database may not be available during install/migrations
        }

        // 3. Ultimate fallback
        return env('DEFAULT_LANGUAGE', 'fr');
    }
}
