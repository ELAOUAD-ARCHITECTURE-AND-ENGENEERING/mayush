<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

class PromotionalCategoryController extends Controller
{
    /**
     * AJAX fetch products for the selected category.
     */
    public function getProducts(Request $request)
    {
        $category_id = $request->category_id;
        if (!$category_id) {
            return '<p class="text-danger text-center">Category ID is missing.</p>';
        }

        $category = Category::find($category_id);
        if (!$category) {
            return '<p class="text-danger text-center">Category not found.</p>';
        }

        // Get all descendant category IDs
        $category_ids = $this->getAllChildrenIds($category);
        $category_ids[] = $category->id;

        // Fetch products that belong to this category or its subcategories
        $products = Product::whereIn('category_id', $category_ids)
            ->where('published', 1)
            ->where('approved', 1)
            ->select('id', 'name', 'unit_price', 'thumbnail_img', 'discount', 'discount_type')
            ->get();

        if ($products->isEmpty()) {
            return '<p class="text-muted text-center py-4">'.translate('No published products found in this category or its subcategories.').'</p>';
        }

        $html = '<div class="table-responsive"><table class="table aiz-table mb-0">';
        $html .= '<thead><tr>';
        $html .= '<th>'.translate('Product').'</th>';
        $html .= '<th>'.translate('Base Price').'</th>';
        $html .= '<th>'.translate('Discount').'</th>';
        $html .= '<th>'.translate('Discount Type').'</th>';
        $html .= '<th class="text-right">'.translate('Action').'</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($products as $product) {
            $image_url = uploaded_asset($product->thumbnail_img);
            if (!$image_url) {
                $image_url = static_asset('assets/img/placeholder.jpg');
            }

            $discount_amount = $product->discount;
            $discount_type = $product->discount_type;

            $html .= '<tr>';
            $html .= '<td>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="'.$image_url.'" class="size-40px img-fit rounded" alt="'.$product->name.'">
                            </div>
                            <div class="col">
                                <span class="text-muted text-truncate-2">'.$product->name.'</span>
                            </div>
                        </div>
                      </td>';
            $html .= '<td>'.single_price($product->unit_price).'</td>';
            $html .= '<td><input type="number" step="0.01" min="0" class="form-control input-discount" value="'.$discount_amount.'"></td>';
            
            $flat_selected = $discount_type == 'amount' ? 'selected' : '';
            $percent_selected = $discount_type == 'percent' ? 'selected' : '';

            $html .= '<td>
                        <select class="form-control select-discount-type">
                            <option value="amount" '.$flat_selected.'>'.translate('Flat').'</option>
                            <option value="percent" '.$percent_selected.'>'.translate('Percent').'</option>
                        </select>
                      </td>';
            $html .= '<td class="text-right">
                        <button type="button" class="btn btn-primary btn-sm btn-update-discount" data-id="'.$product->id.'">'.translate('Update').'</button>
                      </td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    /**
     * AJAX update discount for single product inline.
     */
    public function updateDiscounts(Request $request)
    {
        $product = Product::find($request->product_id);
        if ($product) {
            $product->discount = $request->discount;
            $product->discount_type = $request->discount_type;
            $product->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    private function getAllChildrenIds($category, &$ids = [])
    {
        foreach ($category->categories as $child) {
            $ids[] = $child->id;
            $this->getAllChildrenIds($child, $ids);
        }
        return $ids;
    }
}
