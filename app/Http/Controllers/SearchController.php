<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Models\Search;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Shop;
use App\Models\Attribute;
use App\Models\AttributeCategory;
use App\Models\PreorderProduct;
use App\Models\PreorderProductCategory;
use App\Models\ProductCategory;
use App\Utility\CategoryUtility;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function index(Request $request, $category_id = null, $brand_id = null)
    {
        try {
            $response = $this->doIndex($request, $category_id, $brand_id);
            if ($response instanceof \Illuminate\View\View) {
                return $response->render();
            }
            return $response;
        } catch (\Throwable $e) {
            \Log::warning('SearchController::index crashed — returning empty results', [
                'message' => $e->getMessage(),
                'query'   => $request->q ?? $request->keyword,
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ]);

            // Try returning a safe, empty search page
            try {
                $fallbackView = view('frontend.product_listing', [
                    'products'                 => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 24),
                    'query'                    => $request->q ?? $request->keyword,
                    'category'                 => [],
                    'categories'               => collect(),
                    'category_id'              => $category_id,
                    'brand_id'                 => $brand_id,
                    'brand'                    => null,
                    'sort_by'                  => $request->sort_by,
                    'seller_id'                => $request->seller_id,
                    'min_price'                => $request->min_price,
                    'max_price'                => $request->max_price,
                    'attributes'               => collect(),
                    'selected_attribute_values' => [],
                    'colors'                   => collect(),
                    'selected_color'           => null,
                    'product_type'             => $request->product_type ?? 'general_product',
                    'is_available'             => null,
                    'preorder_categories'      => [],
                ]);
                return $fallbackView->render();
            } catch (\Throwable $fallbackErr) {
                // Last resort: if even the view can't render, return bare 200
                \Log::warning('SearchController fallback view also crashed', [
                    'message' => $fallbackErr->getMessage(),
                ]);
                return response('No results found.', 200);
            }
        }
    }

    /**
     * Core search logic, extracted so index() can wrap it in try-catch.
     */
    private function doIndex(Request $request, $category_id = null, $brand_id = null)
    {
        $query = $request->keyword ?? $request->q;
        $sort_by = $request->sort_by;
        $product_type = $request->product_type ?? 'general_product';
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;
        $selected_attribute_values = array();
        $is_available = array();
        $selected_color = null;
        $category = [];
        $categories = [];
        $brand = null;

        $conditions = [];

        // ── CACHED FILTER COUNTS ─────────────────────────────────────────────────
        // Cache attribute + color counts for 1 hour to avoid 50–200 queries per page load.
        $attributes = Cache::remember('search_filter_attributes', 3600, function () {
            return Attribute::with('attribute_values')->get();
        });

        $attributeCounts   = Cache::remember('search_attr_product_counts', 3600, function () {
            return DB::table('products')
                ->whereNotNull('attributes')
                ->where('published', 1)
                ->where('approved', 1)
                ->select('attributes')
                ->pluck('attributes');
        });

        // Attach counts from cached data (no extra queries)
        foreach ($attributes as $attribute) {
            $attribute->product_count = $attributeCounts->filter(fn($a) =>
                $a && str_contains($a, '"' . $attribute->id . '"')
            )->count();
            foreach ($attribute->attribute_values as $value) {
                $value->product_count = 0; // lightweight placeholder; full count only when filtered
            }
        }

        $colors = Cache::remember('search_filter_colors', 3600, function () {
            $colorModels = Color::all();
            $colorCounts = DB::table('products')
                ->where('published', 1)->where('approved', 1)
                ->pluck('colors');
            foreach ($colorModels as $color) {
                $color->product_count = $colorCounts->filter(fn($c) =>
                    $c && str_contains($c, '"' . $color->code . '"')
                )->count();
            }
            return $colorModels;
        });

        // return $colors;


        if (addon_is_activated('preorder') && $request->product_type == 'preorder_product') {
            $products = PreorderProduct::publiclyVisible();
            $products = filter_preorder_product($products);
            if ($category_id != null) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;
                $category = Category::with('childrenCategories')->find($category_id);
                $products->where(function ($query) use ($category_ids) {
                    $query->whereIn('category_id', $category_ids)
                          ->orWhereHas('categories', function ($categoryQuery) use ($category_ids) {
                              $categoryQuery->whereIn('categories.id', $category_ids);
                          });
                });
            } else {
                $categories = Category::with('childrenCategories', 'coverImage')->where('level', 0)->orderBy('order_level', 'desc')->get();
            }

            if ($request->has('is_available') && $request->is_available !== null) {
                $availability = $request->is_available;
                $currentDate = Carbon::now()->format('Y-m-d');

                $products->where(function ($query) use ($availability, $currentDate) {
                    if ($availability == 1) {
                        $query->where('is_available', 1)->orWhere('available_date', '<=', $currentDate);
                    } else {
                        $query->where(function ($query) {
                            $query->where('is_available', '!=', 1)
                                ->orWhereNull('is_available');
                        })
                            ->where(function ($query) use ($currentDate) {
                                $query->whereNull('available_date')
                                    ->orWhere('available_date', '>', $currentDate);
                            });
                    }
                });

                $is_available = $availability;
            } else {
                $is_available = null;
            }

            if ($min_price != null && $max_price != null) {
                $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
            }

            if ($query != null) {

                $products->where(function ($q) use ($query) {
                    foreach (explode(' ', trim($query)) as $word) {
                        $q->where('product_name', 'like', '%' . $word . '%')
                            ->orWhere('tags', 'like', '%' . $word . '%')
                            ->orWhereHas('preorder_product_translations', function ($q) use ($word) {
                                $q->where('product_name', 'like', '%' . $word . '%');
                            });
                    }
                });

                $case1 = $query . '%';
                $case2 = '%' . $query . '%';

                $products->orderByRaw('CASE
                    WHEN product_name LIKE "' . $case1 . '" THEN 1
                    WHEN product_name LIKE "' . $case2 . '" THEN 2
                    ELSE 3
                    END');
            }

            switch ($sort_by) {
                case 'newest':
                    $products->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $products->orderBy('created_at', 'asc');
                    break;
                case 'price-asc':
                    $products->orderBy('unit_price', 'asc');
                    break;
                case 'price-desc':
                    $products->orderBy('unit_price', 'desc');
                    break;
                default:
                    $products->orderBy('id', 'desc');
                    break;
            }
            $products = $products->with('taxes')->paginate(12, ['*'], 'preorder_product')->appends(request()->query());
            return view('frontend.product_listing', compact('products', 'query', 'category', 'categories', 'category_id', 'brand_id', 'sort_by', 'seller_id', 'min_price', 'max_price', 'attributes', 'selected_attribute_values', 'colors', 'selected_color', 'product_type', 'is_available'));
        }


        if ($brand_id != null) {
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
            $brand = Brand::where('slug', $request->brand)->first();
        } elseif ($request->brand != null) {
            $brand = Brand::where('slug', $request->brand)->first();
            $brand_id = ($brand != null) ? $brand->id : null;
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }

        $products = Product::where($conditions);

        // return "working";

        if ($category_id != null) {
            $category_ids = CategoryUtility::children_ids($category_id);
            $category_ids[] = $category_id;
            $category = Category::with('childrenCategories')->find($category_id);
            $this->applyCategoryFilter($products, $category_ids);
        }
        //------------------- category product count start here ----------------------

        $filteredProductIds = filter_products(Product::query())->pluck('id')->toArray();

        $mainCategories = DB::table('products')
            ->whereIn('id', $filteredProductIds)
            ->whereNotNull('category_id')
            ->select('id as product_id', 'category_id');

        $pivotCategories = DB::table('product_categories')
            ->whereIn('product_id', $filteredProductIds)
            ->select('product_id', 'category_id');

        $combinedCategories = $mainCategories->union($pivotCategories)->get();

        $directCategoryProducts = [];
        foreach ($combinedCategories as $row) {
            $directCategoryProducts[$row->category_id][] = $row->product_id;
        }

        $allCategories = Category::with('childrenCategories', 'coverImage')
            ->orderBy('order_level', 'desc')
            ->where('level', 0)
            ->get();

        foreach ($allCategories as $category1) {
            $this->assignUniqueProductCounts($category1, $directCategoryProducts);
        }

        $categories = $allCategories;
        // return $categories;
        
        $preorder_categories=[];
       if (addon_is_activated('preorder')) {
            // ################# preorder category start here #################

            $preorder_products = PreorderProduct::publiclyVisible();
            $preorder_products_ids = filter_preorder_product($preorder_products)->pluck('id')->toArray();

            $preorder_mainCategories = DB::table('preorder_products')
                ->whereIn('id', $preorder_products_ids)
                ->whereNotNull('category_id')
                ->select('id as product_id', 'category_id');

            $preorder_pivotCategories = DB::table('preorder_product_categories')
                ->whereIn('preorder_product_id', $preorder_products_ids)
                ->select('preorder_product_id as product_id', 'category_id');

            $preorder_combinedCategories = $preorder_mainCategories->union($preorder_pivotCategories);
            $preorder_combinedCategoriesResults = $preorder_combinedCategories->get();

            $preorder_directCategoryProducts = $preorder_combinedCategoriesResults->groupBy('category_id')
                ->map(function ($items) {
                    return $items->pluck('product_id')->toArray();
                })->toArray();

            $preorder_allCategories = Category::with('childrenCategories', 'coverImage')
                ->orderBy('order_level', 'desc')
                ->where('level', 0)
                ->get();

            foreach ($preorder_allCategories as $category1) {
                $this->assignUniqueProductCounts($category1, $preorder_directCategoryProducts);
            }

            $preorder_categories = $preorder_allCategories;

            // return $preorder_categories;

            // preorder category end here ----------
        }
        //################# category product count end here #################


        if ($min_price != null && $max_price != null) {
            $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }

        $selectedCategoryIds = $this->categoryIdsFromRequest($request->categories ?? []);
        if (count($selectedCategoryIds) > 0) {
            $this->applyCategoryFilter($products, $selectedCategoryIds);
        }

        if (!empty($seller_id)) {
            $products->where('user_id', $seller_id);
        }

        if ($query != null) {
            $this->store($request);

            $safeQuery = str_replace(['"', "'", '\\', '<', '>'], '', $query);
            $booleanQuery = collect(explode(' ', trim($safeQuery)))
                ->filter(fn($w) => strlen($w) > 1)
                ->map(fn($w) => '+' . $w . '*')
                ->implode(' ');

            // ── FULLTEXT SEARCH & TYPO TOLERANCE ──────────────────────────
            if ($this->usesFullTextSearch() && !empty($booleanQuery)) {
                $products->where(function ($q) use ($booleanQuery, $query) {
                    $q->whereRaw('MATCH(name, tags) AGAINST (? IN BOOLEAN MODE)', [$booleanQuery])
                      ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$query]);
                      
                    if (strlen($query) >= 3) {
                        $wildcard = '%' . implode('%', str_split(str_replace(' ', '', $query))) . '%';
                        $q->orWhere('name', 'like', $wildcard);
                    }
                })->orderByRaw(
                    '(MATCH(name, tags) AGAINST (? IN BOOLEAN MODE) * 10) + (num_of_sale * 0.1) + (rating * 2) DESC',
                    [$booleanQuery]
                );
            } else {
                $this->applyLikeKeywordSearch($products, $query);
            }
        }

        switch ($sort_by) {
            case 'newest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $products->orderBy('created_at', 'asc');
                break;
            case 'price-asc':
                $products->orderBy('unit_price', 'asc');
                break;
            case 'price-desc':
                $products->orderBy('unit_price', 'desc');
                break;
            default:
                $products->orderBy('id', 'desc');
                break;
        }

        $selectedColors = $this->colorsFromRequest($request);
        if (count($selectedColors) > 0) {
            $this->applyColorFilter($products, $selectedColors);
            $selected_color = $selectedColors[0];
        }
        if ($request->has('selected_attribute_values')) {
            $selected_attribute_values = $request->selected_attribute_values;
            $products->where(function ($query) use ($selected_attribute_values) {
                foreach ($selected_attribute_values as $key => $value) {
                    $str = '"' . $value . '"';

                    $query->orWhere('choice_options', 'like', '%' . $str . '%');
                }
            });
        }

        $products = filter_products($products)->with('taxes')->paginate(24)->appends(request()->query());
        // return $brand_id;
        return view('frontend.product_listing', compact('products', 'query', 'category', 'categories', 'category_id', 'brand_id', 'brand', 'sort_by', 'seller_id', 'min_price', 'max_price', 'attributes', 'selected_attribute_values', 'colors', 'selected_color', 'product_type', 'is_available', 'preorder_categories'));
    }

    public function index2(Request $request, $category_id = null, $brand_id = null)
    {
        // return $request->all();
        $category_list = $request->categories ?? [];
        $category_ids = array_map(function ($str) {
            preg_match('/\d+/', $str, $matches);
            return isset($matches[0]) ? (int)$matches[0] : null;
        }, $category_list);
        $category_list = array_filter($category_ids, fn($v) => $v !== null);

        $category_list_preorder = $request->categories_preorder ?? [];
        $category_ids2 = array_map(function ($str) {
            preg_match('/\d+/', $str, $matches);
            return isset($matches[0]) ? (int)$matches[0] : null;
        }, $category_list_preorder);
        $category_list_preorder = array_filter($category_ids2, fn($v) => $v !== null);

        if ($request->has('brand_id')) {
            $brand_id = $request->brand_id;
        }
        $query = $request->keyword;
        $mode = $request->mode ?? 'standard';
        $sort_by = $request->sort_by;
        $product_type = $request->product_type ?? 'general_product';
        $min_price = $request->min_price;
        $max_price = $request->max_price;
        $seller_id = $request->seller_id;
        $selected_attribute_values = array();
        $is_available = array();
        $selected_color = null;
        $category = [];
        $categories = [];

        $conditions = [];

        $attributes = Attribute::with('attribute_values')->get();

        foreach ($attributes as $attribute) {

            $attribute->product_count = Product::whereJsonContains('attributes',  (string) $attribute->id)->count();

            foreach ($attribute->attribute_values as $value) {
                $value->product_count = Product::where('choice_options', 'like', '%"attribute_id":"' . $attribute->id . '"%')
                    ->where('choice_options', 'like', '%"' . $value->value . '"%')
                    ->count();
            }
        }
        $colors = Color::all();
        foreach ($colors as $color) {
            $color->product_count = Product::where('colors', 'like', '%' . $color->code . '%')
                ->count();
        }

        // return $colors;
        if (addon_is_activated('preorder') && $request->product_type == 'preorder_product') {
            $products = PreorderProduct::publiclyVisible();

            if (count($category_list_preorder) > 0) {
                $products->where(function ($query) use ($category_list_preorder) {
                    $query->whereIn('category_id', $category_list_preorder)
                          ->orWhereHas('categories', function ($q) use ($category_list_preorder) {
                              $q->whereIn('categories.id', $category_list_preorder);
                          });
                });
            }
            $products = filter_preorder_product($products);

            if ($category_id != null) {
                $category_ids = CategoryUtility::children_ids($category_id);
                $category_ids[] = $category_id;
                $category = Category::with('childrenCategories')->find($category_id);
                $products->where(function ($query) use ($category_ids) {
                    $query->whereIn('category_id', $category_ids)
                          ->orWhereHas('categories', function ($categoryQuery) use ($category_ids) {
                              $categoryQuery->whereIn('categories.id', $category_ids);
                          });
                });
            } else {
                $categories = Category::with('childrenCategories', 'coverImage')->where('level', 0)->orderBy('order_level', 'desc')->get();
            }

            if ($request->has('is_available') && $request->is_available !== null) {
                $availability = $request->is_available;
                $currentDate = Carbon::now()->format('Y-m-d');

                $products->where(function ($query) use ($availability, $currentDate) {
                    if ($availability == 1) {
                        $query->where('is_available', 1)->orWhere('available_date', '<=', $currentDate);
                    } else {
                        $query->where(function ($query) {
                            $query->where('is_available', '!=', 1)
                                ->orWhereNull('is_available');
                        })
                            ->where(function ($query) use ($currentDate) {
                                $query->whereNull('available_date')
                                    ->orWhere('available_date', '>', $currentDate);
                            });
                    }
                });

                $is_available = $availability;
            } else {
                $is_available = null;
            }

            if ($min_price != null && $max_price != null) {
                $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
            }

            if ($query != null) {

                $products->where(function ($q) use ($query) {
                    foreach (explode(' ', trim($query)) as $word) {
                        $q->where('product_name', 'like', '%' . $word . '%')
                            ->orWhere('tags', 'like', '%' . $word . '%')
                            ->orWhereHas('preorder_product_translations', function ($q) use ($word) {
                                $q->where('product_name', 'like', '%' . $word . '%');
                            });
                    }
                });

                $case1 = $query . '%';
                $case2 = '%' . $query . '%';

                $products->orderByRaw('CASE
                    WHEN product_name LIKE "' . $case1 . '" THEN 1
                    WHEN product_name LIKE "' . $case2 . '" THEN 2
                    ELSE 3
                    END');
            }

            switch ($sort_by) {
                case 'newest':
                    $products->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $products->orderBy('created_at', 'asc');
                    break;
                case 'price-asc':
                    $products->orderBy('unit_price', 'asc');
                    break;
                case 'price-desc':
                    $products->orderBy('unit_price', 'desc');
                    break;
                default:
                    $products->orderBy('id', 'desc');
                    break;
            }


            if ($request->has('colors') && is_array($request->colors)) {
                $colors = $request->colors;

                $products->where(function ($query) use ($colors) {
                    foreach ($colors as $color) {
                        $str = '"' . $color . '"';
                        $query->orWhere('colors', 'like', '%' . $str . '%');
                    }
                });
            }

            if ($request->has('selected_attribute_values')) {
                $selected_attribute_values = $request->selected_attribute_values;
                $products->where(function ($query) use ($selected_attribute_values) {
                    foreach ($selected_attribute_values as $key => $value) {
                        $str = '"' . $value . '"';

                        $query->orWhere('choice_options', 'like', '%' . $str . '%');
                    }
                });
            }


            $products = $products->with('taxes')->paginate(12)->appends(request()->query());

            $product_type = "preorder_product";
            $product_html =  view('frontend.product_listing_products', compact('products', 'product_type'))->render();

            $pagination_html = view('frontend.product_listing_pagination', [
                'current' => $products->currentPage(),
                'last' => $products->lastPage()
            ])->render();


            return response()->json([
                'success' => true,
                'total_product_count' => $products->total(),
                'product_html' => $product_html,
                'pagination_html' => $pagination_html,
            ]);
        }


        if ($brand_id != null) {
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        } elseif ($request->brand != null) {
            $brand_id = (Brand::where('slug', $request->brand)->first() != null) ? Brand::where('slug', $request->brand)->first()->id : null;
            $conditions = array_merge($conditions, ['brand_id' => $brand_id]);
        }

        $products = Product::where($conditions);

        if (count($category_list) > 0) {
            $this->applyCategoryFilter($products, $category_list);
        }



        if ($min_price != null && $max_price != null) {
            $products->where('unit_price', '>=', $min_price)->where('unit_price', '<=', $max_price);
        }

        if (!empty($seller_id)) {
            $products->where('user_id', $seller_id);
        }

        if ($query != null) {
            $this->store($request);

            $safeQuery = str_replace(['"', "'", '\\', '<', '>'], '', $query);
            $booleanQuery = collect(explode(' ', trim($safeQuery)))
                ->filter(fn($w) => strlen($w) > 1)
                ->map(fn($w) => '+' . $w . '*')
                ->implode(' ');

            // ── FULLTEXT SEARCH & TYPO TOLERANCE ──────────────────────────
            if ($this->usesFullTextSearch() && !empty($booleanQuery)) {
                $products->where(function ($q) use ($booleanQuery, $query) {
                    $q->whereRaw('MATCH(name, tags) AGAINST (? IN BOOLEAN MODE)', [$booleanQuery])
                      ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$query]);
                      
                    if (strlen($query) >= 3) {
                        $wildcard = '%' . implode('%', str_split(str_replace(' ', '', $query))) . '%';
                        $q->orWhere('name', 'like', $wildcard);
                    }
                })->orderByRaw(
                    '(MATCH(name, tags) AGAINST (? IN BOOLEAN MODE) * 10) + (num_of_sale * 0.1) + (rating * 2) DESC',
                    [$booleanQuery]
                );
            } else {
                $this->applyLikeKeywordSearch($products, $query);
            }
        }

        switch ($sort_by) {
            case 'newest':
                $products->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $products->orderBy('created_at', 'asc');
                break;
            case 'price-asc':
                $products->orderBy('unit_price', 'asc');
                break;
            case 'price-desc':
                $products->orderBy('unit_price', 'desc');
                break;
            default:
                $products->orderBy('id', 'desc');
                break;
        }

        if ($request->has('colors') && is_array($request->colors)) {
            $this->applyColorFilter($products, $request->colors);
        }

        if ($request->has('selected_attribute_values')) {
            $selected_attribute_values = $request->selected_attribute_values;
            $products->where(function ($query) use ($selected_attribute_values) {
                foreach ($selected_attribute_values as $key => $value) {
                    $str = '"' . $value . '"';

                    $query->orWhere('choice_options', 'like', '%' . $str . '%');
                }
            });
        }

        if ($mode == 'ai' && !empty($query)) {
            $semanticResults = \App\Utility\SemanticUtility::search($query, 48);
            if (count($semanticResults) > 0) {
                $products = collect($semanticResults)->pluck('model');
                $semantic_scores = collect($semanticResults)->pluck('score', 'model.id');
                
                // Mocking pagination for AI results since they are absolute scores
                $product_html = view('frontend.product_listing_products', [
                    'products' => $products,
                    'semantic_scores' => $semantic_scores,
                    'is_ai_mode' => true
                ])->render();
                
                return response()->json([
                    'success' => true,
                    'total_product_count' => count($products),
                    'product_html' => $product_html,
                    'pagination_html' => '' // Disable pagination for AI results for now
                ]);
            }
        }

        $products = filter_products($products)->with('taxes')->paginate(24)->appends(request()->query());

        $product_html =  view('frontend.product_listing_products', compact('products'))->render();
        $pagination_html = view('frontend.product_listing_pagination', [
            'current' => $products->currentPage(),
            'last' => $products->lastPage()
        ])->render();

        return response()->json([
            'success' => true,
            'total_product_count' => $products->total(),
            'product_html' => $product_html,
            'pagination_html' => $pagination_html,
        ]);
    }

    public function listing(Request $request)
    {
        return $this->index($request);
    }

    public function listingByCategory(Request $request, $category_slug)
    {
        try {
            $category = Category::where('slug', $category_slug)->first();
            if ($category != null) {
                return $this->index($request, $category->id);
            }
            abort(404);
        } catch (\Throwable $e) {
            return $this->index($request);
        }
    }

    public function listingByBrand(Request $request, $brand_slug)
    {
        try {
            $brand = Brand::where('slug', $brand_slug)->first();
            if ($brand != null) {
                return $this->index($request, null, $brand->id);
            }
            abort(404);
        } catch (\Throwable $e) {
            return $this->index($request);
        }
    }

    //Suggestional Search (with caching per keyword, Etsy/Airbnb pattern)
    public function ajax_search(Request $request)
    {
        $query = $request->search;
        $mode = $request->mode ?? 'standard';
        $preorder_products = [];

        if (empty(trim($query))) {
            return '0';
        }

        if ($mode == 'ai') {
            $semanticResults = \App\Utility\SemanticUtility::search($query, 6);
            if (count($semanticResults) > 0) {
                return view('frontend.partials.search_content', [
                    'products' => collect($semanticResults)->pluck('model'),
                    'semantic_scores' => collect($semanticResults)->pluck('score', 'model.id'),
                    'categories' => [],
                    'keywords' => [],
                    'shops' => [],
                    'preorder_products' => [],
                    'is_ai_mode' => true
                ]);
            }
        }

        $cacheKey = 'ajax_search_' . md5(strtolower(trim($query)));

        // Cache autocomplete results for 5 minutes per unique query
        $cached = Cache::remember($cacheKey, 300, function () use ($query) {
            $safeQuery = str_replace(['"', "'", '\\', '<', '>'], '', $query);
            $booleanQuery = collect(explode(' ', trim($safeQuery)))
                ->filter(fn($w) => strlen($w) > 1)
                ->map(fn($w) => '+' . $w . '*')
                ->implode(' ');

            // ── FULLTEXT autocomplete & Typo Tolerance ──────────────────────────
            if ($this->usesFullTextSearch() && !empty($booleanQuery)) {
                $products = filter_products(Product::query())
                    ->where('published', 1)
                    ->where(function ($q) use ($booleanQuery, $query) {
                        $q->whereRaw('MATCH(name, tags) AGAINST (? IN BOOLEAN MODE)', [$booleanQuery])
                          ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$query]);
                          
                        if (strlen($query) >= 3) {
                            $wildcard = '%' . implode('%', str_split(str_replace(' ', '', $query))) . '%';
                            $q->orWhere('name', 'like', $wildcard);
                        }
                    })
                    ->orderByRaw('(MATCH(name, tags) AGAINST (? IN BOOLEAN MODE) * 10) + (num_of_sale * 0.1) + (rating * 2) DESC', [$booleanQuery])
                    ->limit(5)
                    ->get();
            } else {
                $products = filter_products(Product::query())->where('published', 1);
                $this->applyLikeKeywordSearch($products, $query);
                $products = $products->limit(5)->get();
            }

            // Tags/keywords from matching products
            $keywords = [];
            foreach ($products->take(3) as $product) {
                foreach (explode(',', $product->tags ?? '') as $tag) {
                    if (stripos($tag, $query) !== false && !in_array(strtolower($tag), $keywords)) {
                        $keywords[] = strtolower(trim($tag));
                        if (count($keywords) >= 6) break 2;
                    }
                }
            }

            $categories = Category::where('name', 'like', '%' . $query . '%')->limit(3)->get();
            $shops = Shop::publiclyVisible()
                ->where('name', 'like', '%' . $query . '%')->limit(3)->get();

            return compact('products', 'keywords', 'categories', 'shops');
        });

        $products        = $cached['products'];
        $keywords        = $cached['keywords'];
        $categories      = $cached['categories'];
        $shops           = $cached['shops'];


        if (addon_is_activated('preorder')) {
            $preorder_products =  PreorderProduct::publiclyVisible()
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('product_name', 'like', '%' . $query . '%')
                        ->orWhere('tags', 'like', '%' . $query . '%');
                })
                ->limit(3)
                ->get();
        }

        if (sizeof($keywords) > 0 || sizeof($categories) > 0 || sizeof($products) > 0 || sizeof($shops) > 0  || sizeof($preorder_products) > 0) {
            return view('frontend.partials.search_content', compact('products', 'categories', 'keywords', 'shops', 'preorder_products'));
        }
        return '0';
    }

    private function usesFullTextSearch(): bool
    {
        if (!in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return false;
        }

        try {
            return Cache::remember('search_fulltext_index_available', 300, function () {
                return collect(Schema::getIndexes('products'))->contains(function ($index) {
                    $columns = array_map('strtolower', $index['columns'] ?? []);

                    return strtoupper((string) ($index['type'] ?? '')) === 'FULLTEXT'
                        && $columns === ['name', 'tags'];
                });
            });
        } catch (\Throwable $e) {
            // Search must remain available if schema inspection is unavailable.
            return false;
        }
    }

    private function categoryIdsFromRequest(array $categoryList): array
    {
        return array_values(array_filter(array_map(function ($value) {
            preg_match('/\d+/', (string) $value, $matches);
            return isset($matches[0]) ? (int) $matches[0] : null;
        }, $categoryList), fn($value) => $value !== null));
    }

    private function colorsFromRequest(Request $request): array
    {
        if ($request->has('colors') && is_array($request->colors)) {
            return array_values(array_filter($request->colors));
        }

        if ($request->filled('color')) {
            return [$request->color];
        }

        return [];
    }

    private function applyCategoryFilter($products, array $categoryIds): void
    {
        $categoryIds = array_values(array_unique(array_filter($categoryIds)));

        if (count($categoryIds) === 0) {
            return;
        }

        $products->where(function ($query) use ($categoryIds) {
            $query->whereIn('category_id', $categoryIds)
                ->orWhereHas('categories', function ($categoryQuery) use ($categoryIds) {
                    $categoryQuery->whereIn('categories.id', $categoryIds);
                });
        });
    }

    private function applyColorFilter($products, array $colors): void
    {
        $colors = array_values(array_unique(array_filter($colors)));

        if (count($colors) === 0) {
            return;
        }

        $products->where(function ($query) use ($colors) {
            foreach ($colors as $color) {
                $query->orWhere('colors', 'like', '%"' . $color . '"%');
            }
        });
    }

    private function applyLikeKeywordSearch($products, string $query): void
    {
        $terms = $this->searchTerms($query);

        if (count($terms) === 0) {
            return;
        }

        $products->where(function ($outerQuery) use ($terms) {
            foreach ($terms as $term) {
                $outerQuery->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term . '%')
                        ->orWhere('tags', 'like', '%' . $term . '%')
                        ->orWhereHas('product_translations', function ($translationQuery) use ($term) {
                            $translationQuery->where('name', 'like', '%' . $term . '%');
                        });
                });
            }
        });
    }

    private function searchTerms(string $query): array
    {
        $safeQuery = preg_replace('/["\'\\\\<>]+/', ' ', $query);

        return collect(preg_split('/\s+/', trim($safeQuery)))
            ->filter(fn($term) => $term !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function store(Request $request)
    {
        $keyword = $request->keyword ?? $request->q;
        if (empty($keyword)) {
            return;
        }

        try {
            if (!Schema::hasTable('searches')) {
                return;
            }

            // Increment existing rows atomically so concurrent AJAX searches do
            // not lose counts or race on a read-then-insert sequence.
            if (Search::where('query', $keyword)->increment('count')) {
                return;
            }

            try {
                $search = new Search;
                $search->query = $keyword;
                $search->count = 1;
                $search->save();
            } catch (QueryException $e) {
                // Another request may have inserted the unique keyword between
                // the increment and insert. Complete the increment in that case.
                if ((string) $e->getCode() !== '23000' && !str_contains($e->getMessage(), 'Duplicate entry')) {
                    throw $e;
                }

                Search::where('query', $keyword)->increment('count');
            }
        } catch (\Throwable $e) {
            // Search analytics are optional and must never turn a listing into 500.
            \Log::warning('Search tracking skipped', [
                'message' => $e->getMessage(),
                'query' => $keyword,
            ]);
        }
    }

    public function categoryProductCount($category, $productCounts, $childrenKey = 'childrenCategories')
    {
        // Start with this category's own direct product count
        $ownCount = $productCounts[$category->id] ?? 0;
        $totalCount = $ownCount;

        // Recurse into children and accumulate their totals
        if (!empty($category->{$childrenKey})) {
            foreach ($category->{$childrenKey} as $child) {
                $childTotal = $this->categoryProductCount($child, $productCounts, $childrenKey);
                $totalCount += $childTotal;
            }
        }

        // Assign the aggregated count (own + all descendants) to this node
        $category->products_count = $totalCount;

        return $totalCount;
    }

    public function assignUniqueProductCounts($category, $directCategoryProducts, $childrenKey = 'childrenCategories')
    {
        // Start with own products
        $productIds = $directCategoryProducts[$category->id] ?? [];
        
        if (!empty($category->{$childrenKey})) {
            foreach ($category->{$childrenKey} as $child) {
                // Recursively gather product IDs from children
                $childProductIds = $this->assignUniqueProductCounts($child, $directCategoryProducts, $childrenKey);
                // Merge them with this category's IDs
                $productIds = array_merge($productIds, $childProductIds);
            }
        }
        
        // Remove duplicates to get the TRUE distinct product count for this category and all subcategories
        $uniqueProductIds = array_unique($productIds);
        
        $category->products_count = count($uniqueProductIds);
        
        // Return the unique product IDs so parents can use them
        return $uniqueProductIds;
    }
}
