<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerProduct;
use App\Models\CustomerProductTranslation;
use App\Models\Category;
use App\Models\Brand;
use Auth;
use Illuminate\Support\Str;
use App\Utility\CategoryUtility;

class CustomerProductController extends Controller
{
    public function __construct() {
        // Staff Permission Check
        $this->middleware(['permission:view_classified_products'])->only('customer_product_index');
        $this->middleware(['permission:publish_classified_product'])->only('updatePublished');
        $this->middleware(['permission:delete_classified_product'])->only('destroy_by_admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(get_setting('classified_product') != 1){
            return redirect()->route('dashboard');
        }
        
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            flash(translate('Access Denied. Customers cannot manage classified products.'))->error();
            return redirect()->route('dashboard');
        }

        $products = CustomerProduct::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);
        return view('frontend.user.customer.products', compact('products'));
    }

    public function customer_product_index()
    {
        $products = CustomerProduct::orderBy('created_at', 'desc')->paginate(10);
        return view('backend.customer.classified_products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user()->fresh();

        // Access Control: Deny Customers
        if ($user->user_type == 'customer') {
            flash(translate('Access Denied. Customers cannot create classified products.'))->error();
            return redirect()->route('dashboard');
        }

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();

        if ($user->user_type == "seller") {
            return view('frontend.user.customer.product_upload', compact('categories'));
        }

        abort(403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            abort(403, 'Access Denied. Customers cannot store classified products.');
        }

        if (Auth::user()->user_type == 'seller' && (int) Auth::user()->remaining_uploads <= 0) {
            flash(translate('Your classified product upload limit has been reached. Please buy a package.'))->error();
            return back()->withInput();
        }

        $request->validate($this->productRules());

        $customer_product                       = new CustomerProduct;
        $customer_product->name                 = $request->name;
        $customer_product->added_by             = $request->added_by;
        $customer_product->user_id              = Auth::user()->id;
        $customer_product->category_id          = $request->category_id;
        $customer_product->brand_id             = $request->brand_id;
        $customer_product->conditon             = $request->conditon;
        $customer_product->location             = $request->location;
        $customer_product->photos               = $request->photos;
        $customer_product->thumbnail_img        = $request->thumbnail_img;
        $customer_product->unit                 = $request->unit;

        $customer_product->tags                 = implode(',', $this->tagsFromRequest($request));
        $customer_product->description          = $request->description;
        $customer_product->video_provider       = $request->video_provider;
        $customer_product->video_link           = $request->video_link;
        $customer_product->unit_price           = $request->unit_price;
        $customer_product->meta_title           = $request->meta_title;
        $customer_product->meta_description     = $request->meta_description;
        $customer_product->meta_img             = $request->meta_img;
        $customer_product->pdf                  = $request->pdf;
        $customer_product->slug                 = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name)).'-'.Str::random(5));
        
        // Promotion Validation
        if ($request->has('promote_product')) {
            if (Auth::user()->remaining_uploads < 2) {
                 flash(translate('Insufficient credits. You need at least 2 credits for product creation and promotion.'))->error();
                 return back()->withInput();
            }

            // Validate dates are present
            if (!$request->promotion_start_date || !$request->promotion_end_date) {
                flash(translate('Promotion dates are required.'))->error();
                return back()->withInput();
            }
        }

        // Wrap in transaction to ensure atomic credit deduction
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if($customer_product->save()){
                $user = Auth::user();
                $user->remaining_uploads -= 1;

                if ($request->has('promote_product')) {
                    $promotion = new \App\Models\Promotion();
                    $promotion->user_id = $user->id;
                    $promotion->product_id = $customer_product->id;
                    $promotion->tier = $request->promotion_tier;
                    $promotion->start_date = $request->promotion_start_date;
                    $promotion->end_date = $request->promotion_end_date;
                    $promotion->notes = $request->promotion_notes;
                    $promotion->status = 'awaiting_admin_review';
                    $promotion->save();

                    $user->remaining_uploads -= 1;
                }

                $user->save();

                $customer_product_translation               = CustomerProductTranslation::firstOrNew(['lang' => config('app.locale'), 'customer_product_id' => $customer_product->id]);
                $customer_product_translation->name         = $request->name;
                $customer_product_translation->unit         = $request->unit;
                $customer_product_translation->description  = $request->description;
                $customer_product_translation->save();

                \Illuminate\Support\Facades\DB::commit();

                flash(translate('Product has been inserted successfully'))->success();
                if (Auth::user()->user_type == 'seller') {
                    return redirect()->route('seller.promoted_products');
                }
                return redirect()->route('customer_products.index');
            }
            else{
                \Illuminate\Support\Facades\DB::rollBack();
                flash(translate('Something went wrong'))->error();
                return back();
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            flash(translate('Something went wrong: ') . $e->getMessage())->error();
            return back();
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
    public function edit(Request $request, $id)
    {
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            flash(translate('Access Denied. Customers cannot edit classified products.'))->error();
            return redirect()->route('dashboard');
        }

        $product = CustomerProduct::findOrFail($id);

        // Ownership Check: Sellers can only edit their own products
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Access Denied. You can only edit your own products.');
        }

        $categories = Category::where('parent_id', 0)
            ->where('digital', 0)
            ->with('childrenCategories')
            ->get();
        $lang       = $request->lang;
        return view('frontend.user.customer.product_edit', compact('categories', 'product','lang'));
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
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            abort(403, 'Access Denied. Customers cannot update classified products.');
        }

        $customer_product = CustomerProduct::findOrFail($id);

        // Ownership Check: Sellers can only update their own products
        if ($customer_product->user_id !== Auth::id()) {
            abort(403, 'Access Denied. You can only update your own products.');
        }

        $request->validate($this->productRules(false));

        if($request->lang == env("DEFAULT_LANGUAGE")){
            $customer_product->name             = $request->name;
            $customer_product->unit             = $request->unit;
            $customer_product->description      = $request->description;
        }
        $customer_product->user_id              = Auth::user()->id;
        $customer_product->category_id          = $request->category_id;
        $customer_product->brand_id             = $request->brand_id;
        $customer_product->conditon             = $request->conditon;
        $customer_product->location             = $request->location;
        $customer_product->photos               = $request->photos;
        $customer_product->thumbnail_img        = $request->thumbnail_img;

        $customer_product->tags                 = implode(',', $this->tagsFromRequest($request));
        $customer_product->video_provider       = $request->video_provider;
        $customer_product->video_link           = $request->video_link;
        $customer_product->unit_price           = $request->unit_price;
        $customer_product->meta_title           = $request->meta_title;
        $customer_product->meta_description     = $request->meta_description;
        $customer_product->meta_img             = $request->meta_img;
        $customer_product->pdf                  = $request->pdf;
        $customer_product->slug                 = strtolower($request->slug);

        // Promotion Logic (Update/Create)
        if ($request->has('promote_product')) {
            if (Auth::user()->remaining_uploads < 1) { // Assuming update doesn't cost credit, but promotion does?
                 flash(translate('Insufficient credits for promotion.'))->error();
                 return back();
            }

            // Check if already promoted
            $existing_promotion = \App\Models\Promotion::where('product_id', $customer_product->id)
                ->whereIn('status', ['approved', 'awaiting_admin_review'])
                ->first();

            if (!$existing_promotion) {
                 if (!$request->promotion_start_date || !$request->promotion_end_date) {
                    flash(translate('Promotion dates are required.'))->error();
                    return back()->withInput();
                }

                $promotion = new \App\Models\Promotion();
                $promotion->user_id = Auth::user()->id;
                $promotion->product_id = $customer_product->id;
                $promotion->tier = $request->promotion_tier;
                $promotion->start_date = $request->promotion_start_date;
                $promotion->end_date = $request->promotion_end_date;
                $promotion->notes = $request->promotion_notes;
                $promotion->status = 'awaiting_admin_review';
                $promotion->save();
                
                $user = Auth::user();
                $user->remaining_uploads -= 1;
                $user->save();
            }
        }

        if($customer_product->save()){

            $customer_product_translation               = CustomerProductTranslation::firstOrNew(['lang' => $request->lang, 'customer_product_id' => $customer_product->id]);
            $customer_product_translation->name         = $request->name;
            $customer_product_translation->unit         = $request->unit;
            $customer_product_translation->description  = $request->description;
            $customer_product_translation->save();

            flash(translate('Product has been updated successfully'))->success();
            if (Auth::user()->user_type == 'seller') {
                return redirect()->route('seller.promoted_products');
            }
            return back();
        }
        else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            abort(403, 'Access Denied. Customers cannot delete classified products.');
        }

        $product = CustomerProduct::findOrFail($id);

        // Ownership Check: Sellers can only delete their own products
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Access Denied. You can only delete your own products.');
        }

        $product->customer_product_translations()->delete();

        if (CustomerProduct::destroy($id)) {
            flash(translate('Product has been deleted successfully'))->success();
            return redirect()->route('customer_products.index');
        }
    }

    public function destroy_by_admin($id)
    {
        $product = CustomerProduct::findOrFail($id);
        $product->customer_product_translations()->delete();

        if (CustomerProduct::destroy($id)) {
            return back();
        }
    }

    public function updateStatus(Request $request)
    {
        $product = CustomerProduct::findOrFail($request->id);
        $product->status = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function updatePublished(Request $request)
    {
        $product = CustomerProduct::findOrFail($request->id);
        $product->published = $request->status;
        if($product->save()){
            return 1;
        }
        return 0;
    }

    public function customer_products_listing(Request $request)
    {
        return $this->search($request);
    }

    public function customer_product($slug)
    {
        if(get_setting('classified_product') != 1){
            return redirect('/');
        }
        $detailedProduct  = CustomerProduct::where('slug', $slug)->first();
        if($detailedProduct!=null){
            return view('frontend.customer_product_details', compact('detailedProduct'));
        }
        abort(404);
    }

    public function search(Request $request)
    {
        if(get_setting('classified_product') != 1){
            return redirect('/');
        }

        $brand_id = (Brand::where('slug', $request->brand)->first() != null) ? Brand::where('slug', $request->brand)->first()->id : null;
        $category_id = (Category::where('slug', $request->category)->first() != null) ? Category::where('slug', $request->category)->first()->id : null;
        $sort_by = $request->sort_by;
        $condition = $request->condition;

        $conditions = ['published' => 1, 'status' => 1];

        if($brand_id != null){
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }

        $customer_products = CustomerProduct::where($conditions);

        if($category_id != null){
            $category_ids = CategoryUtility::children_ids($category_id);
            $category_ids[] = $category_id;

            $customer_products = $customer_products->whereIn('category_id', $category_ids);
        }

        if($sort_by != null){
            switch ($sort_by) {
                case '1':
                    $customer_products->orderBy('created_at', 'desc');
                    break;
                case '2':
                    $customer_products->orderBy('created_at', 'asc');
                    break;
                case '3':
                    $customer_products->orderBy('unit_price', 'asc');
                    break;
                case '4':
                    $customer_products->orderBy('unit_price', 'desc');
                    break;
                case '5':
                    $customer_products->where('conditon', 'new');
                    break;
                case '6':
                    $customer_products->where('conditon', 'used');
                    break;
                default:
                    // code...
                    break;
            }
        }

        if($condition != null){
            $customer_products->where('conditon', $condition);
        }

        $customer_products = $customer_products->paginate(12)->appends(request()->query());

        return view('frontend.customer_product_listing', compact('customer_products', 'category_id', 'brand_id', 'sort_by', 'condition'));
    }

    public function store_promotion(Request $request)
    {
        // Access Control: Deny Customers
        if (Auth::user()->user_type == 'customer') {
            abort(403, 'Access Denied. Customers cannot promote classified products.');
        }

        // Input validation
        $request->validate([
            'product_id' => 'required|integer|exists:customer_products,id',
            'tier'       => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after:start_date',
            'notes'      => 'nullable|string|max:500',
        ]);

        // C-4 FIX: Ownership check — sellers can only promote their own products
        $product = \App\Models\CustomerProduct::findOrFail($request->product_id);
        if ($product->user_id !== Auth::user()->id) {
            abort(403, 'Access Denied. You can only promote your own products.');
        }

        if (Auth::user()->remaining_uploads <= 0) {
             flash(translate('Insufficient credits. Please top up your account.'))->error();
             return back();
        }

        $overlap = \App\Models\Promotion::where('product_id', $request->product_id)
            ->whereIn('status', ['approved', 'awaiting_admin_review'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                      ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                      });
            })
            ->exists();

        if ($overlap) {
            flash(translate('Promotion dates overlap with an existing promotion.'))->error();
            return back();
        }

        // Wrap in transaction for atomic credit deduction
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $promotion = new \App\Models\Promotion();
            $promotion->user_id = Auth::user()->id;
            $promotion->product_id = $request->product_id;
            $promotion->tier = $request->tier;
            $promotion->start_date = $request->start_date;
            $promotion->end_date = $request->end_date;
            $promotion->notes = $request->notes;
            $promotion->status = 'awaiting_admin_review';
            $promotion->save();

            $user = Auth::user();
            $user->remaining_uploads -= 1;
            $user->save();

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            flash(translate('Something went wrong: ') . $e->getMessage())->error();
            return back();
        }

        flash(translate('Promotion requested successfully.'))->success();

        if (Auth::user()->user_type == 'seller') {
            return redirect()->route('seller.promoted_products');
        }
        return back();
    }

    private function productRules(bool $creating = true): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'conditon' => ['required', 'in:new,used'],
            'location' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'tags' => ['nullable', 'array'],
            'photos' => ['nullable', 'string'],
            'thumbnail_img' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_img' => ['nullable', 'string'],
            'pdf' => ['nullable', 'string'],
            'video_provider' => ['nullable', 'in:youtube,dailymotion,vimeo'],
            'video_link' => ['nullable', 'string', 'max:255'],
            'promotion_start_date' => ['nullable', 'date'],
            'promotion_end_date' => ['nullable', 'date', 'after:promotion_start_date'],
        ];
    }

    private function tagsFromRequest(Request $request): array
    {
        $tagPayload = $request->input('tags.0');

        if (empty($tagPayload)) {
            return [];
        }

        $decodedTags = json_decode($tagPayload);

        if (!is_array($decodedTags)) {
            return [];
        }

        return collect($decodedTags)
            ->pluck('value')
            ->filter()
            ->values()
            ->all();
    }
}
