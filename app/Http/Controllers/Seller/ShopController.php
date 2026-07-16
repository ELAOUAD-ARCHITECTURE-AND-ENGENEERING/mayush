<?php

namespace App\Http\Controllers\Seller;

use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\State;
use App\Models\User;
use App\Notifications\ShopVerificationNotification;
use Auth;
use Illuminate\Support\Facades\Notification;

class ShopController extends Controller
{
    public function index()
    {
        $shop = Auth::user()->shop;
        return view('seller.shop', compact('shop'));
    }

    public function update(Request $request)
    {
        // Seller self-service must always be scoped to the authenticated
        // seller. The hidden shop_id is only a legacy form field and is not an
        // authorization boundary.
        $shop = Auth::user()->shop;
        abort_unless($shop, 404);

        // Seller onboarding documents are handled exclusively by
        // SellerDocument. Preserve non-file business settings, but do not
        // accept retired verification uploads that would be written below
        // public/ or create a second approval workflow.
        $legacyVerificationFileFields = ['certificate', 'seller_photo', 'id_card', 'gstin_certificate'];
        $hasLegacyVerificationFile = collect($legacyVerificationFileFields)
            ->contains(fn (string $field): bool => $request->hasFile($field));

        if ($hasLegacyVerificationFile || $request->filled('live_selfie')) {
            flash(translate('Seller verification is now completed from the onboarding page.'))->warning();
            return redirect()->route('seller.onboarding.index');
        }

        if ($request->has('name') && $request->has('address')) {
            if ($request->has('shipping_cost')) {
                $shop->shipping_cost = $request->shipping_cost;
            }

            $shop->name             = $request->name;
            $shop->address          = $request->address;
            $shop->phone            = $request->phone;
            $shop->slug             = preg_replace('/\s+/', '-', $request->name) . '-' . $shop->id;
            $shop->meta_title       = $request->meta_title;
            $shop->meta_description = $request->meta_description;
            $shop->logo             = $request->logo;
        }
        
        if ($request->has('artisan_story') || $request->has('brand_philosophy') || $request->has('workshop_video_url') || $request->has('story_title')) {
            $shop->artisan_story = $request->artisan_story;
            $shop->brand_philosophy = $request->brand_philosophy;
            $shop->workshop_video_url = $request->workshop_video_url;
            $shop->story_title = $request->story_title;
            $shop->story_content = $request->story_content;
            $shop->hero_media_id = $request->hero_media_id;
            $shop->artisan_quote = $request->artisan_quote;
            $shop->gallery_json = $request->gallery_json;
        }

        if ($request->has('delivery_pickup_longitude') && $request->has('delivery_pickup_latitude'))
        {
            $shop->delivery_pickup_longitude    = $request->delivery_pickup_longitude;
            $shop->delivery_pickup_latitude     = $request->delivery_pickup_latitude;
        } 
        elseif ($request->has('facebook') || $request->has('google') || $request->has('twitter') ||$request->has('youtube') || $request->has('instagram'))
        {
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->google = $request->google;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;
        }

        $business_info = json_decode($shop->business_info, true) ?? [];
        if ($request->has('certificate_number')) {

            $business_info['certificate_number'] = $request->certificate_number;
            $business_info['country'] = Country::find($request->country_id)?->name;
            $business_info['state']   = State::find($request->state_id)?->name;
        }

        if (addon_is_activated('gst_system') && $request->has('gstin_number')) {
            $business_info['gstin'] = $request->gstin_number;
        }
        $shop->business_info = json_encode($business_info);


        if ($shop->save()) {
            flash(translate('Your Shop has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function bannerUpdate(Request $request){
        // Do not allow a seller to update another shop by changing the legacy
        // hidden shop_id form value.
        $shop = Auth::user()->shop;
        abort_unless($shop, 404);
        $shop->top_banner_image     = $request->top_banner_image;
        $shop->top_banner_link      = $request->top_banner_link;
        $shop->slider_images        = $request->slider_images;
        $shop->slider_links         = $request->slider_links;
        $shop->banner_full_width_1_images   = $request->banner_full_width_1_images;
        $shop->banner_full_width_1_links    = $request->banner_full_width_1_links;
        $shop->banners_half_width_images    = $request->banners_half_width_images;
        $shop->banners_half_width_links     = $request->banners_half_width_links;
        $shop->banner_full_width_2_images   = $request->banner_full_width_2_images;
        $shop->banner_full_width_2_links    = $request->banner_full_width_2_links;
        if ($shop->save()) {
            flash(translate('Your Shop banners has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    public function verify_form()
    {
        flash(translate('Seller verification is now completed from the onboarding page.'))->info();
        return redirect()->route('seller.onboarding.index');
    }

    public function verify_form_store(Request $request)
    {
        // Retained as a compatibility endpoint so old links do not fail, but
        // it must never create public verification uploads or mutate the
        // legacy verification workflow.
        flash(translate('Seller verification is now completed from the onboarding page.'))->warning();
        return redirect()->route('seller.onboarding.index');
    }

    public function show()
    {
    }

    public function categoriesWiseCommission(Request $request){
        $sort_search =null;
        $categories = Category::orderBy('order_level', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $categories = $categories->where('name', 'like', '%'.$sort_search.'%');
        }
        $categories = $categories->paginate(15);
        return view('seller.categoryWise_commission', compact('categories'))->render();
    }
}
