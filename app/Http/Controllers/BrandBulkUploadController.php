<?php

namespace App\Http\Controllers;

use App\Models\BrandsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Throwable;

class BrandBulkUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:brand_bulk_upload'])->only('index');
    }

    public function index()
    {
        return view('backend.product.brand_bulk_upload.index');
    }

    public function bulk_upload(Request $request)
    {
        if (!extension_loaded('zip')){
            flash(translate('Please enable the Zip extension'))->error();
            return back();
        }

        $request->validate([
            'bulk_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                Excel::import(new BrandsImport, $request->file('bulk_file'));
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
            flash(translate('Brand import failed. Please check the file and try again.'))->error();

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
