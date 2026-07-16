<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shop;
use App\Models\SellerDocument;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ShopVerificationNotification;
use App\Services\PreorderService;
use App\Utility\EmailUtility;
use Cache;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_seller|view_all_seller_rating_and_followers'])->only('index');
        $this->middleware(['permission:add_seller'])->only('create');
        $this->middleware(['permission:view_seller_profile'])->only('sellerProfile');
        $this->middleware(['permission:login_as_seller'])->only('login');
        $this->middleware(['permission:pay_to_seller'])->only('payment_modal');
        $this->middleware(['permission:edit_seller'])->only('edit');
        $this->middleware(['permission:delete_seller'])->only('destroy');
        $this->middleware(['permission:ban_seller'])->only('ban');
        $this->middleware(['permission:edit_seller_custom_followers'])->only('editSellerCustomFollowers');
        $this->middleware(['permission:view_pending_seller'])->only('pendingSellers');
        $this->middleware(['permission:view_pending_seller|view_seller_profile'])->only([
            'showDocuments',
            'downloadDocument',
            'show_verification_request',
            'verification_info_modal',
        ]);
        $this->middleware(['permission:approve_seller'])->only([
            'approveApplication',
            'rejectApplication',
            'reviewDocument',
            'updateApproved',
            'approve_seller',
            'UpdateSellerRegistration',
        ]);
        $this->middleware(['permission:mark_seller_suspected'])->only('suspicious');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = $request->search ?? null;
        $approved = $request->approved_status ?? null;
        $verification_status =  $request->verification_status ?? null;

        $shops = Shop::whereIn('user_id', function ($query) {
            $query->select('id')
                ->from(with(new User)->getTable())
                ->where('user_type', 'seller');
        })->latest();

        if ($sort_search != null || $verification_status != null) {
            $user_ids = User::where('user_type', 'seller');
            if ($sort_search != null) {
                $user_ids = $user_ids->where(function ($user) use ($sort_search) {
                    $user->where('name', 'like', '%' . $sort_search . '%')
                        ->orWhere('email', 'like', '%' . $sort_search . '%')
                        ->orWhere('phone', 'like', '%' . $sort_search . '%');
                });
            }
            if ($verification_status != null) {
                $user_ids = $verification_status == 'verified' ? $user_ids->where('email_verified_at', '!=', null) : $user_ids->where('email_verified_at', null);
            }
            $user_ids = $user_ids->pluck('id')->toArray();
            $shops = $shops->where(function ($shops) use ($user_ids) {
                $shops->whereIn('user_id', $user_ids);
            });
        }
        if ($approved != null) {
            $shops = $approved
                ? $shops->where('approval_status', 'approved')
                : $shops->where('approval_status', '!=', 'approved');
        }
        $shops = $shops->paginate(15);
        return view('backend.sellers.index', compact('shops', 'sort_search', 'approved', 'verification_status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.sellers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'shop_name' => 'max:200',
                'address' => 'max:500',
            ],
            [
                'name.required' => translate('Name is required'),
                'name.max' => translate('Max 255 Character'),
                'email.required' => translate('Email is required'),
                'email.email' => translate('Email must be a valid email address'),
                'email.unique' => translate('An user exists with this email'),
                'shop_name.max' => translate('Max 200 Character'),
                'address.max' => translate('Max 255 Character'),
            ]
        );

        DB::beginTransaction();
        try {
            $password = substr(hash('sha512', rand()), 0, 8);

            $user           = new User;
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->user_type = "seller";
            $user->password = Hash::make($password);
            $user->is_intern = $request->has('is_intern') ? 1 : 0;
            $user->save();

            $shop           = new Shop;
            $shop->user_id  = $user->id;
            $shop->name     = $request->shop_name;
            $shop->address  = $request->address;
            $shop->slug     = 'demo-shop-' . $user->id;
            // Administrator-created sellers follow the same restricted
            // onboarding workflow as self-registered sellers.
            $shop->registration_approval = 0;
            $shop->approval_status = 'pending';
            $shop->save();

            try {
                EmailUtility::seller_registration_email('registration_from_system_email_to_seller', $user, $password);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Seller Registration Email Failed: " . $e->getMessage());
                flash(translate('Registration failed: Email could not be sent. Please check SMTP settings.'))->error();
                return back()->withInput();
            }

            // Verification email send
            if (get_setting('email_verification') != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
            } else {
                EmailUtility::email_verification($user, 'seller');
            }

            // Seller Account Opening Email to Admin
            if ((get_email_template_data('seller_reg_email_to_admin', 'status') == 1)) {
                try {
                    EmailUtility::seller_registration_email('seller_reg_email_to_admin', $user, null);
                } catch (\Exception $e) {
                    Log::warning("Admin notification email failed: " . $e->getMessage());
                }
            }

            DB::commit();

            app(\App\Services\SellerOnboardingNotifier::class)->registrationCompleted($shop);

            flash(translate('Seller has been added successfully'))->success();
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Seller Registration Failed: " . $e->getMessage());
            flash(translate('Something went wrong: ') . $e->getMessage())->error();
            return back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shop = Shop::findOrFail(decrypt($id));
        return view('backend.sellers.edit', compact('shop'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $user = $shop->user;
        $user->name = $request->name;
        $user->email = $request->email;
        if (strlen($request->password) > 0) {
            $user->password = Hash::make($request->password);
        }
        $user->is_intern = $request->has('is_intern') ? 1 : 0;
        if ($user->save()) {
            if ($request->has('artisan_story')) {
                $shop->artisan_story = $request->artisan_story;
            }
            if ($request->has('brand_philosophy')) {
                $shop->brand_philosophy = $request->brand_philosophy;
            }
            if ($request->has('workshop_video_url')) {
                $shop->workshop_video_url = $request->workshop_video_url;
            }
            if ($shop->save()) {
                flash(translate('Seller has been updated successfully'))->success();
                return redirect()->route('sellers.index');
            }
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);

        // Seller Product and product related data delete
        $products = $shop->user->products;
        foreach ($products as $product) {
            $product_id = $product->id;
            $product->product_translations()->delete();
            $product->categories()->detach();
            $product->stocks()->delete();
            $product->taxes()->delete();
            $product->frequently_bought_products()->delete();
            $product->last_viewed_products()->delete();
            $product->flash_deal_products()->delete();

            if ($product->delete()) {
                Cart::where('product_id', $product_id)->delete();
                Wishlist::where('product_id', $product_id)->delete();
            }
        }

        $orders = Order::where('user_id', $shop->user_id)->get();

        foreach ($orders as $key => $order) {
            OrderDetail::where('order_id', $order->id)->delete();
        }
        Order::where('user_id', $shop->user_id)->delete();

        // If Preorder addon is installed, delete preorder products and related data.
        if (Addon::where('unique_identifier', 'preorder')->first()) {
            $preorderProducts = $shop->user->preorderProducts;
            foreach ($preorderProducts as $preorderProduct) {
                (new PreorderService)->productdestroy($preorderProduct->id);
            }
        }

        User::destroy($shop->user->id);

        if (Shop::destroy($id)) {
            flash(translate('Seller has been deleted successfully'))->success();
            return redirect()->route('sellers.index');
        } else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function bulk_seller_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $shop_id) {
                $this->destroy($shop_id);
            }
        }

        return 1;
    }

    public function show_verification_request($id)
    {
        return $this->legacyOnboardingDisabled(request(), (int) $id);
    }

    public function approve_seller(Request $request, $id)
    {
        return $this->legacyOnboardingDisabled($request, (int) $id);
    }

    public function reject_seller(Request $request, $id)
    {
        return $this->legacyOnboardingDisabled($request, (int) $id);
    }

    // ─── NEW ONBOARDING WORKFLOW METHODS ────────────────────────────────────

    public function showDocuments($id)
    {
        $shop = Shop::with(['documents' => fn ($query) => $query->orderByDesc('version')->orderByDesc('id')])->findOrFail($id);
        $reviewHistory = \App\Models\AuditLog::where('target_user_id', $shop->user_id)
            ->whereIn('action_type', ['seller_documents_uploaded', 'seller_application_approved', 'seller_application_rejected', 'seller_document_reviewed'])
            ->with('admin')
            ->latest()
            ->get();
        return response()->json([
            'html' => view('backend.sellers.documents_modal', compact('shop', 'reviewHistory'))->render()
        ]);
    }

    public function approveApplication(Request $request, $id)
    {
        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $shop = null;
        $approvalBlocked = false;
        DB::transaction(function () use ($id, $validated, &$shop, &$approvalBlocked) {
            $shop = Shop::with('documents')->lockForUpdate()->findOrFail($id);
            $missing = $shop->missingRequiredDocumentTypes();
            $seller = $shop->user;
            if ($missing !== []
                || !$seller
                || $seller->user_type !== 'seller'
                || $seller->banned) {
                $approvalBlocked = true;
                return;
            }

            $previousStatus = $shop->approval_status;
            $shop->approval_status = 'approved';
            $shop->registration_approval = 1;
            $shop->rejection_reason = null;
            $shop->admin_note = $validated['admin_note'] ?? null;
            $shop->reviewed_at = now();
            $shop->reviewed_by = auth()->id();
            $shop->save();

            \App\Models\AuditLog::create([
                'admin_user_id'  => auth()->id(),
                'target_user_id' => $shop->user_id,
                'action_type'    => 'seller_application_approved',
                'description'    => "Seller onboarding status changed from {$previousStatus} to approved.",
                'ip_address'     => request()->ip(),
            ]);
        });

        if ($approvalBlocked) {
            flash(translate('Approval is blocked because required documents are missing or not approved.'))->error();
            return back();
        }

        Cache::forget('verified_sellers_id');
        app(\App\Services\StorefrontCacheService::class)->bump();

        app(\App\Services\SellerOnboardingNotifier::class)->applicationApproved($shop);

        flash(translate('Seller application approved successfully.'))->success();
        return back();
    }

    public function rejectApplication(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $shop = DB::transaction(function () use ($id, $request) {
            $shop = Shop::lockForUpdate()->findOrFail($id);
            $previousStatus = $shop->approval_status;
            $shop->approval_status = 'rejected';
            $shop->registration_approval = 0; // Backward compatibility only.
            $shop->rejection_reason = $request->rejection_reason;
            $shop->admin_note = $request->input('admin_note') ?: $request->rejection_reason;
            $shop->reviewed_at = now();
            $shop->reviewed_by = auth()->id();
            $shop->save();

            \App\Models\AuditLog::create([
                'admin_user_id'  => auth()->id(),
                'target_user_id' => $shop->user_id,
                'action_type'    => 'seller_application_rejected',
                'description'    => "Seller onboarding status changed from {$previousStatus} to rejected. Reason: " . $request->rejection_reason,
                'ip_address'     => request()->ip(),
            ]);

            return $shop;
        });
        Cache::forget('verified_sellers_id');
        app(\App\Services\StorefrontCacheService::class)->bump();

        app(\App\Services\SellerOnboardingNotifier::class)->applicationRejected($shop, $request->rejection_reason);

        flash(translate('Seller application rejected successfully.'))->success();
        return back();
    }

    public function reviewDocument(Request $request, SellerDocument $document)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'rejected' && blank($validated['rejection_reason'] ?? null)) {
            return back()->withErrors(['rejection_reason' => translate('A rejection reason is required.')]);
        }

        $shop = $document->shop;
        $latestDocument = $shop->documents()
            ->where('document_type', $document->document_type)
            ->orderByDesc('version')
            ->orderByDesc('id')
            ->first();

        if (!$latestDocument || (int) $latestDocument->id !== (int) $document->id) {
            return back()->withErrors(['document' => translate('Only the latest document version can be reviewed.')]);
        }

        [$shop, $documentStatus] = DB::transaction(function () use ($document, $validated, $shop) {
            $lockedShop = Shop::lockForUpdate()->findOrFail($shop->id);
            $lockedDocument = SellerDocument::lockForUpdate()->findOrFail($document->id);
            $latestDocument = $lockedShop->documents()
                ->where('document_type', $lockedDocument->document_type)
                ->orderByDesc('version')
                ->orderByDesc('id')
                ->first();

            if (!$latestDocument || (int) $latestDocument->id !== (int) $lockedDocument->id) {
                return [null, null];
            }

            $previousDocumentStatus = $lockedDocument->status ?? 'pending';
            $previousShopStatus = $lockedShop->approval_status;

            $lockedDocument->status = $validated['status'];
            $lockedDocument->rejection_reason = $validated['status'] === 'rejected' ? $validated['rejection_reason'] : null;
            $lockedDocument->reviewed_at = now();
            $lockedDocument->reviewed_by = auth()->id();
            $lockedDocument->save();

            $lockedShop->reviewed_at = now();
            $lockedShop->reviewed_by = auth()->id();
            $lockedShop->refreshOnboardingReviewState();
            $lockedShop->save();

            \App\Models\AuditLog::create([
                'admin_user_id' => auth()->id(),
                'target_user_id' => $lockedShop->user_id,
                'action_type' => 'seller_document_reviewed',
                'description' => "Document {$lockedDocument->document_type} status changed from {$previousDocumentStatus} to {$lockedDocument->status}; seller status changed from {$previousShopStatus} to {$lockedShop->approval_status}.",
                'ip_address' => request()->ip(),
            ]);

            return [$lockedShop, $lockedDocument->status];
        });

        if (!$shop) {
            return back()->withErrors(['document' => translate('Only the latest document version can be reviewed.')]);
        }

        Cache::forget('verified_sellers_id');
        app(\App\Services\StorefrontCacheService::class)->bump();

        if ($documentStatus === 'rejected') {
            $reviewedDocument = SellerDocument::find($document->id);
            if ($reviewedDocument) {
                app(\App\Services\SellerOnboardingNotifier::class)->correctionRequired(
                    $shop,
                    $reviewedDocument,
                    (string) $validated['rejection_reason']
                );
            }
        }

        return back()->with('success', translate('Document review updated successfully.'));
    }

    public function downloadDocument(SellerDocument $document)
    {
        $this->authorize('view', $document);
        $path = $document->safeStoragePath();
        abort_unless($path !== null && Storage::disk('seller_documents')->exists($path), 404);

        $downloadName = (string) Str::of(basename($document->original_name ?: 'seller-document'))
            ->replaceMatches('/[^A-Za-z0-9._-]/', '_')
            ->limit(180, '');

        $contentType = in_array($document->mime_type, [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ], true) ? $document->mime_type : 'application/octet-stream';

        return Storage::disk('seller_documents')->download(
            $path,
            $downloadName,
            ['Content-Type' => $contentType]
        );
    }


    public function payment_modal(Request $request)
    {
        $shop = shop::findOrFail($request->id);
        return view('backend.sellers.payment_modal', compact('shop'));
    }

    public function verification_info_modal(Request $request)
    {
        return $this->legacyOnboardingDisabled($request, (int) $request->id);
    }

    public function updateApproved(Request $request)
    {
        return $this->legacyOnboardingDisabled($request, (int) $request->id);
    }

    public function login($id)
    {
        $shop = Shop::findOrFail(decrypt($id));
        $user  = $shop->user;
        auth()->login($user, true);

        return redirect()->route('seller.dashboard');
    }

    public function ban($id)
    {
        [$shop, $suspended] = DB::transaction(function () use ($id) {
            $shop = Shop::lockForUpdate()->findOrFail($id);
            $user = User::lockForUpdate()->findOrFail($shop->user_id);
            $suspended = (bool) !$user->banned;
            $user->banned = $suspended ? 1 : 0;
            $user->save();
            $shop->setRelation('user', $user);
            return [$shop, $suspended];
        });

        Cache::forget('verified_sellers_id');
        Cache::forget('internal_sellers_id');
        app(\App\Services\StorefrontCacheService::class)->bump();

        app(\App\Services\SellerOnboardingNotifier::class)->accessChanged($shop, $suspended);
        flash($suspended
            ? translate('Seller has been banned successfully.')
            : translate('Seller has been unbanned successfully.'))
            ->success();

        return back();
    }

    // Seller Based Commission
    public function sellerBasedCommission(Request $request)
    {
        $sort_search = $request->search ?? null;
        $approved = $request->approved_status ?? null;
        $verification_status =  $request->verification_status ?? null;

        $shops = Shop::whereIn('user_id', function ($query) {
            $query->select('id')
                ->from(with(new User)->getTable())
                ->where('user_type', 'seller');
        })->latest();

        if ($sort_search != null || $verification_status != null) {
            $user_ids = User::where('user_type', 'seller');
            if ($sort_search != null) {
                $user_ids = $user_ids->where(function ($user) use ($sort_search) {
                    $user->where('name', 'like', '%' . $sort_search . '%')
                        ->orWhere('email', 'like', '%' . $sort_search . '%')
                        ->orWhere('phone', 'like', '%' . $sort_search . '%');
                });
            }
            if ($verification_status != null) {
                $user_ids = $verification_status == 'verified' ? $user_ids->where('email_verified_at', '!=', null) : $user_ids->where('email_verified_at', null);
            }
            $user_ids = $user_ids->pluck('id')->toArray();
            $shops = $shops->where(function ($shops) use ($user_ids) {
                $shops->whereIn('user_id', $user_ids);
            });
        }
        if ($approved != null) {
            $shops = $approved
                ? $shops->where('approval_status', 'approved')
                : $shops->where('approval_status', '!=', 'approved');
        }
        $shops = $shops->paginate(15);
        return view('backend.sellers.seller_based_commission.set_commission', compact('shops', 'sort_search', 'approved', 'verification_status'));
    }



    public function setSellerBasedCommission(Request $request)
    {
        if ($request->seller_ids != null) {
            foreach (explode(",", $request->seller_ids) as $shop) {
                $shop = Shop::where('id', $shop)->first();
                $shop->commission_percentage = $request->commission_percentage;
                $shop->save();
            }
            flash(translate('Seller commission is added successfully.'))->success();
        } else {
            flash(translate('Something went wrong!.'))->warning();
        }
        return back();
    }

    public function setSellerCommission(Request $request)
    {
        if ($request->seller_id != null) {
            $shop = Shop::where('id', $request->seller_id)->first();
            $shop->commission_percentage = $request->commission_percentage;
            $shop->save();

            return 1;
        } else {
            return 0;
        }
    }

    // Edit Seller Custom Followers
    public function editSellerCustomFollowers(Request $request)
    {
        $shop = Shop::where('id', $request->shop_id)->first();
        $shop->custom_followers = $request->custom_followers;
        $shop->save();
        flash(translate('Seller custom follower has been updated successfully.'))->success();
        return back();
    }

    public function pendingSellers(Request $request)
    {
        $sort_search = $request->search ?? null;
        $shops = Shop::whereIn('approval_status', ['pending', 'under_review', 'rejected'])
            ->with('user')
            ->latest();

        if ($sort_search != null) {
            $user_ids = User::where('user_type', 'seller')
                ->where(function ($query) use ($sort_search) {
                    $query->where('name', 'like', '%' . $sort_search . '%')
                        ->orWhere('email', 'like', '%' . $sort_search . '%')
                        ->orWhere('phone', 'like', '%' . $sort_search . '%');
                })
                ->pluck('id')
                ->toArray();
            $shops = $shops->whereIn('user_id', $user_ids);
        }

        $shops = $shops->paginate(15);

        return view('backend.sellers.pending_seller', compact('shops', 'sort_search'));
    }

    public function UpdateSellerRegistration(Request $request)
    {
        return $this->legacyOnboardingDisabled($request, (int) $request->id);
    }

    private function legacyOnboardingDisabled(Request $request, ?int $shopId = null)
    {
        $payload = [
            'message' => translate('The legacy seller verification workflow has been retired. Use seller onboarding document review.'),
            'error' => 'seller_onboarding_legacy_flow_disabled',
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, 410);
        }

        flash($payload['message'])->warning();
        return redirect()->route('sellers.registration_pending', $shopId ? ['review_shop' => $shopId] : []);
    }

    public function sellerProfile(Request $request)
    {
        $shop_id = decrypt($request->id);
        $shop = Shop::findOrFail($shop_id);
        $shop->last_login = $this->getsellerLastLogin($shop->user_id);
        $addresses = $shop->user->addresses->where('set_default', 0);
        $default_shipping_address = $shop->user->addresses()->where('set_default', 1)->first();
        $products = Product::where('user_id', $shop->user_id)->where('digital', 0)->where('auction_product', 0)->where('wholesale_product', 0)->orderBy('created_at', 'desc');
        if ($request->has('search')) {
            $search = $request->search;
            $products = $products->where('name', 'like', '%' . $search . '%');
        }
        $products = $products->paginate(2);
        return view('backend.sellers.profile.index', compact('shop', 'addresses', 'default_shipping_address', 'products'));
    }

    public function getSellerProfileTab(Shop $shop, Request $request)
    {
        $tab = $request->get('tab', 'overview');
        $page = $request->get('page', 1);
        $addresses = $shop->user->addresses->where('set_default', 0);
        $default_shipping_address = $shop->user->addresses()->where('set_default', 1)->first();
        $shop->last_login = $this->getsellerLastLogin($shop->user_id);
        $payments = Payment::where('seller_id', $shop->user_id)->orderBy('created_at', 'desc')->paginate(15);
        $products = Product::where('user_id', $shop->user_id)->where('digital', 0)->where('auction_product', 0)->where('wholesale_product', 0)->orderBy('created_at', 'desc')->paginate(15);
        $type = 'SellerProfile';
        $unpaid_order_payment_notification = get_notification_type('complete_unpaid_order_payment', 'type');
        $orders = Order::where('seller_id', $shop->user_id)
            ->orderBy('id', 'desc')
            ->select('orders.id')
            ->distinct()->paginate(15);
        $html = view('backend.sellers.profile.seller_' . $tab, compact('products', 'shop', 'addresses', 'default_shipping_address', 'page', 'orders', 'type', 'unpaid_order_payment_notification', 'payments'))->render();
        return response()->json(['html' => $html]);
    }

    private function getsellerLastLogin($user_id)
    {
        $logFile = storage_path('logs/seller_login.log');
        $lastLoginTime = null;

        if (File::exists($logFile)) {
            $lines = array_reverse(File::lines($logFile)->toArray());

            foreach ($lines as $line) {
                if (str_contains($line, '"user_id":' . $user_id)) {

                    $jsonStart = strpos($line, '{');
                    if ($jsonStart !== false) {
                        $jsonData = json_decode(substr($line, $jsonStart), true);
                        if ($jsonData && isset($jsonData['time'])) {
                            $lastLoginTime = Carbon::parse($jsonData['time']);
                            break;
                        }
                    }
                }
            }
            return $lastLoginTime;
        }
        return null;
    }

    public function suspicious($id)
    {
        $user = User::findOrFail(decrypt($id));

        if ($user->is_suspicious == 1) {
            $user->is_suspicious = 0;
            flash(translate('Sellert unsuspected  Successfully'))->success();
        } else {
            $user->is_suspicious = 1;
            flash(translate('Seller suspected Successfully'))->success();
        }

        $user->save();

        return back();
    }

    public function deleteVerificationFile(Request $request)
    {
        return $this->legacyOnboardingDisabled($request, (int) $request->input('shop_id'));
    }

    public function resendVerification($id)
    {
        $shop = Shop::findOrFail($id);
        $user = $shop->user;
        if ($user->email != null) {
            EmailUtility::email_verification($user, 'seller');
            flash(translate('Verification email has been sent successfully'))->success();
        } else {
            flash(translate('Seller email not found'))->error();
        }
        return back();
    }

    /**
     * Show seller profile info modal (AJAX).
     */
    public function profile_modal(Request $request)
    {
        $shop = Shop::findOrFail($request->id);
        return view('backend.sellers.profile_modal', compact('shop'));
    }
}
