<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\SliderCollection;
use Cache;

class SliderController extends Controller
{
    public function sliders()
    {
        $lang = request()->header('App-Language') ?: app()->getLocale();
        $dbLang = $lang === 'ar' ? 'ma' : $lang;

        $heroService = app(\App\Services\StorefrontHeroImageService::class);
        $firstValidHero = $heroService->firstValidHero($dbLang) ?: $heroService->firstValidHero($lang) ?: $heroService->firstValidHero();
        $defaultImageId = $firstValidHero ? $firstValidHero->id : 3163;

        $get_images = get_setting('home_slider_images', null, $dbLang) 
            ?: get_setting('home_slider_images', null, $lang) 
            ?: get_setting('home_slider_images');
        $rawImages = $get_images != null ? (is_array($get_images) ? $get_images : json_decode($get_images, true)) : [];
        if (empty($rawImages)) {
            $get_images = get_setting('home_slider_images');
            $rawImages = $get_images != null ? (is_array($get_images) ? $get_images : json_decode($get_images, true)) : [];
        }

        $images = [];
        foreach ($rawImages as $imgId) {
            $upload = \App\Models\Upload::find((int) $imgId);
            if ($upload && file_exists(public_path($upload->file_name))) {
                $images[] = $upload->id;
            } else {
                $images[] = $defaultImageId;
            }
        }
        if (empty($images)) {
            $images[] = $defaultImageId;
        }

        $get_links = get_setting('home_slider_links', null, $dbLang) 
            ?: get_setting('home_slider_links', null, $lang) 
            ?: get_setting('home_slider_links');
        $links = $get_links != null ? (is_array($get_links) ? $get_links : json_decode($get_links, true)) : [];

        $get_titles = get_setting('home_slider_titles', null, $dbLang) 
            ?: get_setting('home_slider_titles', null, $lang) 
            ?: get_setting('home_slider_titles');
        $titles = $get_titles != null ? (is_array($get_titles) ? $get_titles : json_decode($get_titles, true)) : [];

        $get_descriptions = get_setting('home_slider_descriptions', null, $dbLang) 
            ?: get_setting('home_slider_descriptions', null, $lang) 
            ?: get_setting('home_slider_descriptions');
        $descriptions = $get_descriptions != null ? (is_array($get_descriptions) ? $get_descriptions : json_decode($get_descriptions, true)) : [];

        $get_cta_texts = get_setting('home_slider_cta_texts', null, $dbLang) 
            ?: get_setting('home_slider_cta_texts', null, $lang) 
            ?: get_setting('home_slider_cta_texts');
        $cta_texts = $get_cta_texts != null ? (is_array($get_cta_texts) ? $get_cta_texts : json_decode($get_cta_texts, true)) : [];

        $get_cta_links = get_setting('home_slider_cta_links', null, $dbLang) 
            ?: get_setting('home_slider_cta_links', null, $lang) 
            ?: get_setting('home_slider_cta_links');
        $cta_links = $get_cta_links != null ? (is_array($get_cta_links) ? $get_cta_links : json_decode($get_cta_links, true)) : [];

        $sliders = [];
        for ($i = 0; $i < count($images); $i++) {
            $sliders[$i] = [
                'image' => $images[$i] ?? $defaultImageId,
                'link' => $links[$i] ?? null,
                'title' => $titles[$i] ?? null,
                'description' => $descriptions[$i] ?? null,
                'cta_text' => $cta_texts[$i] ?? null,
                'cta_link' => $cta_links[$i] ?? null,
            ];
        }

        return new SliderCollection($sliders);
    }

    public function bannerOne()
    {
        $getImages = get_setting('home_banner1_images', null, request()->header('App-Language'));
        $images = $getImages != null ? json_decode($getImages, true) : [];
        $getLinks = get_setting('home_banner1_links', null, request()->header('App-Language'));
        $links = ($getImages != null && $getLinks != null) ? json_decode($getLinks, true) : [];

        $banners = [];
        for ($i = 0; $i < count($images); $i++) {
            $banners[$i] = ['link' => $links[$i], "image" => $images[$i]];
        }
        return new SliderCollection($banners);
    }

    public function bannerTwo()
    {
        $getImages = get_setting('home_banner2_images', null, request()->header('App-Language'));
        $images = $getImages != null ? json_decode($getImages, true) : [];
        $getLinks = get_setting('home_banner2_links', null, request()->header('App-Language'));
        $links = ($getImages != null && $getLinks != null) ? json_decode($getLinks, true) : [];

        $banners = [];
        for ($i = 0; $i < count($images); $i++) {
            $banners[$i] = ['link' => $links[$i], "image" => $images[$i]];
        }
        return new SliderCollection($banners);
    }

    public function bannerThree()
    {
        $getImages = get_setting('home_banner3_images', null, request()->header('App-Language'));
        $images = $getImages != null ? json_decode($getImages, true) : [];
        $getLinks = get_setting('home_banner3_links', null, request()->header('App-Language'));
        $links = ($getImages != null && $getLinks != null) ? json_decode($getLinks, true) : [];

        $banners = [];
        for ($i = 0; $i < count($images); $i++) {
            $banners[$i] = ['link' => $links[$i], "image" => $images[$i]];
        }
        return new SliderCollection($banners);
    }
}
