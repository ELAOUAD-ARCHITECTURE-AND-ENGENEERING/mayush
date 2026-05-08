<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\ProductsImport;
use App\Models\ProductsExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Auth;
use MyCLabs\Enum\Enum;
use Throwable;


class ProductBulkUploadController extends Controller
{
    public function __construct()
    {

        $this->middleware(['permission:product_bulk_import'])->only('index');
        $this->middleware(['permission:product_bulk_export'])->only('export');
    }

    public function index()
    {
        if (Auth::user()->user_type == 'seller') {
            if (Auth::user()->shop->verification_status) {
                return view('seller.product_bulk_upload.index');
            } else {
                flash(translate('Your shop is not verified yet!'))->warning();
                return back();
            }
        } elseif (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('backend.product.bulk_upload.index');
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products.xlsx');
    }

    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category', [
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand', [
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
    }

    public function pdf_download_seller()
    {
        $users = User::where('user_type', 'seller')->get();

        return PDF::loadView('backend.downloads.user', [
            'users' => $users,
        ], [], [])->download('user.pdf');
    }

    public function bulk_upload(Request $request)
    {
        $request->validate([
            'bulk_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                Excel::import(new ProductsImport, $request->file('bulk_file'));
            });
        } catch (ExcelValidationException $e) {
            $this->flashExcelValidationErrors($e);

            return back()->withInput();
        } catch (LaravelValidationException $e) {
            foreach ($e->errors() as $messages) {
                foreach ($messages as $message) {
                    flash($message)->error();
                }
            }

            return back()->withInput();
        } catch (Throwable $e) {
            report($e);
            flash(translate('Product import failed. Please check the file and try again.'))->error();

            return back()->withInput();
        }

        return back();
    }

    private function flashExcelValidationErrors(ExcelValidationException $e): void
    {
        $failures = $e->failures();
        $shown = 0;

        foreach ($failures as $failure) {
            flash(translate('Row') . ' ' . $failure->row() . ': ' . implode(' ', $failure->errors()))->error();
            $shown++;

            if ($shown >= 8) {
                break;
            }
        }

        if (count($failures) > $shown) {
            flash((count($failures) - $shown) . ' ' . translate('more import validation errors were hidden. Fix the first rows and upload again.'))->warning();
        }
    }

    /**
     * Download a CSV template for product import.
     */
    public function import_product($type)
    {
        $filePath = base_path('resources/csv/' . $type . '.csv');
        if (file_exists($filePath)) {
            return response()->download($filePath);
        }
        flash(translate('CSV template not found.'))->error();
        return back();
    }

    /**
     * Download CSV template for a specific vendor's products.
     */
    public function import_vendor_product($id)
    {
        flash(translate('Vendor product CSV download is not yet available.'))->info();
        return back();
    }
}
