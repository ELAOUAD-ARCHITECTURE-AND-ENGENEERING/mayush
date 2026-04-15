<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\PointAssignmentLog;
use App\Models\PointTemplate;
use App\Services\PointManagementService;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class PointManagementController extends Controller
{
    protected $pointService;

    public function __construct(PointManagementService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Display the main Dashboard and Advanced Form
     */
    public function dashboard(Request $request)
    {
        $categories = Category::where('parent_id', 0)
            ->with('childrenCategories')
            ->get();
        $brands = Brand::all();
        $templates = PointTemplate::where('status', 1)->get();

        $query = Product::with('categories')->where('added_by', 'admin');

        // Advanced Filtering
        if ($request->has('category_id') && $request->category_id != null) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('brand_id') && $request->brand_id != null) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->has('min_price') && $request->min_price != null) {
            $query->where('unit_price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != null) {
            $query->where('unit_price', '<=', $request->max_price);
        }
        if ($request->has('search') && $request->search != null) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->paginate(15);

        return view('backend.loyalty_points.dashboard', compact('products', 'categories', 'brands', 'templates'));
    }

    /**
     * Submit Bulk Points
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'assignment_type' => 'required',
            'point_value' => 'required'
        ]);

        $pointValue = $request->point_value;
        $updatesCount = 0;

        if ($request->assignment_type == 'category' && $request->category_assign_id) {
            $updatesCount = $this->pointService->assignPointsByCategory($request->category_assign_id, $pointValue);
            flash(translate("Successfully assigned points to category subtree. Updated {$updatesCount} products."))->success();
        } elseif ($request->assignment_type == 'selected' && $request->product_ids) {
            $ids = explode(',', $request->product_ids);
            $updatesCount = $this->pointService->assignPointsByProductIds($ids, $pointValue, 'multi-select');
            flash(translate("Successfully assigned points to {$updatesCount} selected products."))->success();
        } else {
            flash(translate('Invalid assignment parameters'))->error();
        }

        return back();
    }

    /**
     * Templates CRUD
     */
    public function templates()
    {
        $templates = PointTemplate::orderBy('id', 'desc')->get();
        return view('backend.loyalty_points.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage_of_price',
            'value' => 'required|numeric|min:0',
            'min_threshold' => 'nullable|numeric|min:0',
            'max_threshold' => 'nullable|numeric|min:0',
        ]);

        PointTemplate::create($request->all());
        flash(translate('Point Template created successfully.'))->success();
        return back();
    }
    
    public function destroyTemplate($id)
    {
        PointTemplate::findOrFail($id)->delete();
        flash(translate('Template deleted successfully'))->success();
        return back();
    }

    /**
     * View Audit History
     */
    public function history()
    {
        $logs = PointAssignmentLog::with('admin')->orderBy('id', 'desc')->paginate(20);
        return view('backend.loyalty_points.history', compact('logs'));
    }

    /**
     * Rollback a bulk assignment
     */
    public function rollback($id)
    {
        $updatesCount = $this->pointService->rollbackLog($id);
        
        if ($updatesCount !== false) {
            flash(translate("Rollback successful. {$updatesCount} products restored to their previous point values."))->success();
        } else {
            flash(translate('Rollback failed. Log might be corrupt or already rolled back.'))->error();
        }

        return back();
    }

    /**
     * CSV Export
     */
    public function csvExport()
    {
        $products = Product::select('id', 'name', 'unit_price', 'earn_point')->where('added_by', 'admin')->get();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=loyalty_points_export_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array('Product ID', 'Product Name', 'Unit Price', 'Current Loyalty Points'));

            foreach ($products as $product) {
                fputcsv($file, array(
                    $product->id,
                    $product->name,
                    $product->unit_price,
                    $product->earn_point
                ));
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * CSV Import
     */
    public function csvImport(Request $request)
    {
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));

            if (count($data) > 1) {
                $productUpdates = [];
                // Skip header row
                for ($i = 1; $i < count($data); $i++) {
                    $productId = $data[$i][0];
                    $points = (int) $data[$i][3]; // 4th column is points
                    
                    if ($productId && $points >= 0) {
                        $productUpdates[$productId] = $points;
                    }
                }

                if (!empty($productUpdates)) {
                    $keys = array_keys($productUpdates);
                    
                    // Because assignPointsByProductIds requires a uniform point value for the array, 
                    // and a CSV has distinct values per product, we've extended the service logic inline here
                    // just for CSV to record 1 bulk audit log.
                    
                    $products = Product::whereIn('id', $keys)->get();
                    $oldState = [];
                    $count = 0;
                    
                    \Illuminate\Support\Facades\DB::beginTransaction();
                    foreach ($products as $p) {
                        $newPt = $productUpdates[$p->id];
                        if ($p->earn_point != $newPt) {
                            $oldState[$p->id] = $p->earn_point;
                            $p->earn_point = $newPt;
                            $p->save();
                            $count++;
                        }
                    }
                    
                    if ($count > 0) {
                        PointAssignmentLog::create([
                            'admin_id' => auth()->id(),
                            'action_type' => 'csv_import',
                            'affected_products_count' => count($oldState),
                            'payload_backup' => json_encode($oldState)
                        ]);
                    }
                    \Illuminate\Support\Facades\DB::commit();

                    flash(translate("CSV Import successful. {$count} product points updated."))->success();
                    return back();
                }
            }
        }

        flash(translate('Invalid CSV File.'))->error();
        return back();
    }
}
