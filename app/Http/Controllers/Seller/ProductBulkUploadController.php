<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use Auth;
use App\Models\ProductsImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Throwable;

class ProductBulkUploadController extends Controller
{
    public function index()
    {
        if(Auth::user()->shop->verification_status){
            return view('seller.product.product_bulk_upload.index');
        }
        else{
            flash(translate('Your shop is not verified yet!'))->warning();
            return back();
        }
    }

    public function pdf_download_category()
    {
        $categories = Category::all();

        return PDF::loadView('backend.downloads.category',[
            'categories' => $categories,
        ], [], [])->download('category.pdf');
    }

    public function pdf_download_brand()
    {
        $brands = Brand::all();

        return PDF::loadView('backend.downloads.brand',[
            'brands' => $brands,
        ], [], [])->download('brands.pdf');
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

}
