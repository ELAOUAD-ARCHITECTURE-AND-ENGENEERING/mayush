<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCollectionController extends Controller
{
    public function index()
    {
        $collections = ProductCollection::latest()->paginate(20);

        return view('backend.product_collections.index', compact('collections'));
    }

    public function create()
    {
        return $this->form(new ProductCollection());
    }

    public function store(Request $request)
    {
        $collection = ProductCollection::create($this->validatedData($request));
        $this->syncProducts($collection, $request->input('product_ids', []));

        flash(translate('Product collection created successfully.'))->success();

        return redirect()->route('product-collections.edit', $collection);
    }

    public function edit(ProductCollection $product_collection)
    {
        return $this->form($product_collection);
    }

    public function update(Request $request, ProductCollection $product_collection)
    {
        $product_collection->update($this->validatedData($request, $product_collection));
        $this->syncProducts($product_collection, $request->input('product_ids', []));

        flash(translate('Product collection updated successfully.'))->success();

        return redirect()->route('product-collections.edit', $product_collection);
    }

    public function destroy(ProductCollection $product_collection)
    {
        $product_collection->products()->detach();
        $product_collection->delete();

        flash(translate('Product collection deleted successfully.'))->success();

        return redirect()->route('product-collections.index');
    }

    private function form(ProductCollection $collection)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $sellers = User::whereIn('user_type', ['seller', 'admin'])->orderBy('name')->get();
        $products = Product::isApprovedPublished()->orderByDesc('id')->limit(500)->get();

        return view('backend.product_collections.form', compact('collection', 'categories', 'brands', 'sellers', 'products'));
    }

    private function validatedData(Request $request, ?ProductCollection $collection = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_collections', 'slug')->ignore($collection?->id)],
            'description' => ['nullable', 'string'],
            'mode' => ['required', Rule::in(['manual', 'dynamic', 'hybrid'])],
            'category_ids' => ['nullable', 'array'],
            'brand_ids' => ['nullable', 'array'],
            'seller_ids' => ['nullable', 'array'],
            'tags' => ['nullable', 'string'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'default_sort' => ['required', Rule::in(['newest', 'oldest', 'popular', 'price-asc', 'price-desc'])],
            'hero_image' => ['nullable'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_image' => ['nullable'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $requestedSlug = $data['slug'];
        $data['slug'] = Str::slug($requestedSlug ?: $data['name']);
        if (!$requestedSlug) {
            $baseSlug = $data['slug'];
            $suffix = 2;
            while (ProductCollection::where('slug', $data['slug'])
                ->when($collection, fn ($query) => $query->where('id', '!=', $collection->id))
                ->exists()) {
                $data['slug'] = $baseSlug . '-' . $suffix++;
            }
        }
        $data['category_ids'] = array_values(array_filter($data['category_ids'] ?? []));
        $data['brand_ids'] = array_values(array_filter($data['brand_ids'] ?? []));
        $data['seller_ids'] = array_values(array_filter($data['seller_ids'] ?? []));
        $data['status'] = $request->boolean('status');
        $data['show_best_selling'] = $request->boolean('show_best_selling');
        $data['show_recently_viewed'] = $request->boolean('show_recently_viewed');

        return $data;
    }

    private function syncProducts(ProductCollection $collection, array $productIds): void
    {
        $sync = [];
        foreach (array_values(array_unique(array_filter($productIds))) as $index => $productId) {
            $sync[(int) $productId] = ['sort_order' => $index];
        }

        $collection->products()->sync($sync);
    }
}
