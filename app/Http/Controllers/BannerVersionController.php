<?php

namespace App\Http\Controllers;

use App\Models\BannerTextVersion;
use App\Models\BusinessSetting;
use App\Services\BannerTextSanitizerService;
use App\Services\BannerTextVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BannerVersionController extends Controller
{
    public function index(Request $request, string $settingKey, BannerTextSanitizerService $sanitizer): JsonResponse
    {
        abort_unless($sanitizer->isBannerTextSetting($settingKey), 404);

        $lang = $request->query('lang');

        $versions = BannerTextVersion::query()
            ->with('user:id,name')
            ->where('setting_key', $settingKey)
            ->when($lang, function ($query) use ($lang) {
                $query->where('lang', $lang);
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (BannerTextVersion $version): array {
                return [
                    'id' => $version->id,
                    'setting_key' => $version->setting_key,
                    'lang' => $version->lang,
                    'value' => json_decode($version->value, true) ?: [],
                    'changed_by' => $version->user?->name,
                    'created_at' => $version->created_at?->toIso8601String(),
                ];
            });

        return response()->json(['versions' => $versions]);
    }

    public function restore(
        BannerTextVersion $version,
        BannerTextSanitizerService $sanitizer,
        BannerTextVersionService $versions
    ): JsonResponse {
        abort_unless($sanitizer->isBannerTextSetting($version->setting_key), 404);

        $setting = BusinessSetting::query()
            ->where('type', $version->setting_key)
            ->when($version->lang === null, function ($query) {
                $query->whereNull('lang');
            }, function ($query) use ($version) {
                $query->where('lang', $version->lang);
            })
            ->first();

        $sanitizedValue = $sanitizer->sanitizeStoredValue($version->value);

        if ($setting) {
            if ($setting->value !== $sanitizedValue) {
                $versions->snapshot($setting->type, $setting->lang, $setting->value, auth()->id());
                $setting->value = $sanitizedValue;
                $setting->save();
            }
        } else {
            BusinessSetting::create([
                'type' => $version->setting_key,
                'lang' => $version->lang,
                'value' => $sanitizedValue,
            ]);
        }

        Artisan::call('cache:clear');

        return response()->json([
            'message' => translate('Banner text version restored successfully'),
            'value' => json_decode($sanitizedValue, true) ?: [],
        ]);
    }
}
