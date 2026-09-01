<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PolicyCollection;
use App\Models\Page;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function sellerPolicy()
    {
        return new PolicyCollection(Page::where('type', 'seller_policy_page')->orWhere('slug', 'seller-policy')->get());
    }

    public function supportPolicy()
    {
        return new PolicyCollection(Page::where('type', 'support_policy_page')->orWhere('slug', 'support-policy')->get());
    }

    public function returnPolicy()
    {
        return new PolicyCollection(Page::where('type', 'return_policy_page')->orWhere('slug', 'return-policy')->get());
    }

    public function privacyPolicy()
    {
        return new PolicyCollection(Page::where('type', 'privacy_policy_page')->orWhere('slug', 'privacy-policy')->get());
    }

    public function termsConditions()
    {
        return new PolicyCollection(Page::where('type', 'terms_conditions_page')->orWhere('slug', 'terms')->get());
    }
}

