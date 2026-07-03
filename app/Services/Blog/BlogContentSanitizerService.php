<?php

namespace App\Services\Blog;

use Throwable;

class BlogContentSanitizerService
{
    private const ALLOWED_TAGS = '<p><br><h2><h3><h4><ul><ol><li><strong><b><em><i><u><a><img><figure><figcaption><blockquote><table><thead><tbody><tr><th><td><hr>';

    public function sanitize(?string $html): string
    {
        $html = (string) $html;

        if ($html === '') {
            return '';
        }

        if (class_exists(\HTMLPurifier::class) && class_exists(\HTMLPurifier_Config::class)) {
            try {
                $config = \HTMLPurifier_Config::createDefault();
                $config->set('HTML.Allowed', 'p,br,h2,h3,h4,ul,ol,li,strong,b,em,i,u,a[href|title|target|rel],img[src|alt|title|width|height|loading],figure,figcaption,blockquote,table,thead,tbody,tr,th,td,hr');
                $config->set('Attr.AllowedFrameTargets', ['_blank']);
                $config->set('URI.AllowedSchemes', [
                    'http' => true,
                    'https' => true,
                    'mailto' => true,
                ]);
                $config->set('Cache.SerializerPath', storage_path('framework/cache'));

                return (new \HTMLPurifier($config))->purify($html);
            } catch (Throwable) {
                return $this->fallbackSanitize($html);
            }
        }

        return $this->fallbackSanitize($html);
    }

    private function fallbackSanitize(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("|\').*?\1/is', '', $html) ?? '';
        $html = preg_replace('/\s+(href|src)\s*=\s*("|\')\s*javascript:.*?\2/is', '', $html) ?? '';

        return strip_tags($html, self::ALLOWED_TAGS);
    }
}
