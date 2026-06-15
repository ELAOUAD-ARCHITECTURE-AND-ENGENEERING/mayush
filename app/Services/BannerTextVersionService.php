<?php

namespace App\Services;

use App\Models\BannerTextVersion;

class BannerTextVersionService
{
    private const RETAINED_VERSIONS = 20;

    public function snapshot(string $settingKey, ?string $lang, ?string $value, ?int $userId): ?BannerTextVersion
    {
        if ($value === null) {
            return null;
        }

        $version = BannerTextVersion::create([
            'setting_key' => $settingKey,
            'lang' => $lang,
            'value' => $value,
            'changed_by' => $userId,
        ]);

        $this->prune($settingKey, $lang);

        return $version;
    }

    private function prune(string $settingKey, ?string $lang): void
    {
        $query = BannerTextVersion::query()
            ->where('setting_key', $settingKey)
            ->when($lang === null, function ($query) {
                $query->whereNull('lang');
            }, function ($query) use ($lang) {
                $query->where('lang', $lang);
            });

        $retainedIds = (clone $query)
            ->orderByDesc('id')
            ->limit(self::RETAINED_VERSIONS)
            ->pluck('id');

        $query->whereNotIn('id', $retainedIds)->delete();
    }
}
